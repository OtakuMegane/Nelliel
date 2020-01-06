<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class CAPTCHA
{
    protected $domain;
    protected $database;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
    }

    public function generate()
    {
        // Pretty basic CAPTCHA
        // We'll leave making a better one to someone who really knows the stuff

        $generated = nel_plugins()->processHook('nel-captcha-generate', [$this->domain]);

        if ($generated)
        {
            return;
        }

        $this->cleanup();

        $captcha_text = '';
        $character_set = 'bcdfghjkmnpqrstvwxyz23456789';
        $set_array = utf8_split($character_set);
        $characters_limit = 5;
        $selected_indexes = array_rand($set_array, $characters_limit);

        foreach ($selected_indexes as $index)
        {
            $captcha_text .= $set_array[$index];
        }

        $font_file = BASE_PATH . ASSETS_DIR . '/fonts/Halogen.ttf';
        $image_width = 250;
        $image_height = 80;
        $font_size = $image_height * 0.5;
        $text_box = imageftbbox($font_size, 0, $font_file, $captcha_text);
        $x_margin = $image_width - $text_box[4];
        $y_margin = $image_height - $text_box[5];
        $character_spacing = ($x_margin / ($characters_limit + 2));
        $captcha_image = imagecreatetruecolor($image_width, $image_height);

        $background_color = imagecolorallocate($captcha_image, 230, 230, 230);
        imagefill($captcha_image, 0, 0, $background_color);

        $line_colors = array();
        $line_colors[] = imagecolorallocate($captcha_image, 150, 150, 0);
        $line_colors[] = imagecolorallocate($captcha_image, 120, 175, 180);
        $line_colors[] = imagecolorallocate($captcha_image, 190, 150, 125);
        $line_colors_size = count($line_colors);

        for ($i = 0; $i < 8; $i ++)
        {
            $line_color = $line_colors[rand(0, $line_colors_size - 1)];
            imagesetthickness($captcha_image, rand(1, 5));
            imageline($captcha_image, 0, rand(0, $image_height), $image_width, rand(0, $image_height), $line_color);
        }

        $x = $x_margin - ($character_spacing * $characters_limit);
        $y = $y_margin / 2;

        $text_colors = array();
        $text_colors[] = imagecolorallocate($captcha_image, 200, 100, 0);
        $text_colors[] = imagecolorallocate($captcha_image, 70, 125, 180);
        $text_colors[] = imagecolorallocate($captcha_image, 140, 100, 125);
        $text_colors_size = count($text_colors);

        foreach ($selected_indexes as $index)
        {
            $character = $set_array[$index];
            $box = imageftbbox($font_size, 0, $font_file, $character);
            $size = $font_size - rand(0, intval($font_size * 0.35));
            $angle = rand(0, 50) - 25;
            $color = $text_colors[rand(0, $text_colors_size - 1)];
            imagefttext($captcha_image, $size, $angle, $x, $y + rand(0, 5), $color, $font_file, $character);
            $x += $box[4] + $character_spacing;
        }

        $captcha_key = substr(sha1(random_bytes(16)), -12);
        setrawcookie('captcha-key', $captcha_key, time() + 3600, '/');
        header("Content-Type: image/png");
        imagepng($captcha_image);

        $captcha_data = array();
        $captcha_data['key'] = $captcha_key;
        $captcha_data['text'] = $captcha_text;
        $captcha_data['case_sensitive'] = 0;
        $captcha_data['time_created'] = time();
        $captcha_data['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $this->store($captcha_data);
    }

    public function store(array $captcha_data)
    {
        $prepared = $this->database->prepare(
                'INSERT INTO "' . CAPTCHA_TABLE .
                '" ("key", "text", "case_sensitive", "time_created", "ip_address")
								VALUES (:key, :text, :case_sensitive, :time_created, :ip_address)');
        $prepared->bindParam(':key', $captcha_data['key'], PDO::PARAM_STR);
        $prepared->bindParam(':text', $captcha_data['text'], PDO::PARAM_STR);
        $prepared->bindParam(':case_sensitive', $captcha_data['case_sensitive'], PDO::PARAM_INT);
        $prepared->bindParam(':time_created', $captcha_data['time_created'], PDO::PARAM_STR);
        $prepared->bindParam(':ip_address', $captcha_data['ip_address'], PDO::PARAM_LOB);
        $this->database->executePrepared($prepared);
    }

    public function verify(string $key, string $answer)
    {
        $verified = nel_plugins()->processHook('nel-captcha-verify', [$this->domain]);

        if ($verified)
        {
            return true;
        }

        $prepared = $this->database->prepare('SELECT * FROM "' . CAPTCHA_TABLE . '" WHERE "key" = ? AND "text" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$key, $answer], PDO::FETCH_ASSOC);

        if ($result === false)
        {
            return false;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . CAPTCHA_TABLE . '" WHERE "key" = ? AND "text" = ?');
        $this->database->executePreparedFetch($prepared, [$key, $answer], PDO::FETCH_ASSOC);
        return true;
    }

    public function cleanup()
    {
        $done = nel_plugins()->processHook('nel-captcha-cleanup', [$this->domain]);

        if ($done)
        {
            return;
        }

        $expiration = time() - 3600;
        $prepared = $this->database->prepare('DELETE FROM "' . CAPTCHA_TABLE . '" WHERE "time_created" < ?');
        $this->database->executePrepared($prepared, [$expiration]);
    }

    public function verifyReCaptcha()
    {
        $verified = nel_plugins()->processHook('nel-verify-recaptcha', [$this->domain]);

        if ($verified)
        {
            return;
        }

        if (!isset($_POST['g-recaptcha-response']))
        {
            return false;
        }

        $site_domain = new DomainSite($this->database);
        $response = $_POST['g-recaptcha-response'];
        $result = file_get_contents(
                'https://www.google.com/recaptcha/api/siteverify?secret=' . $site_domain->setting(
                        'recaptcha_sekrit_key') . '&response=' . $response);
        $verification = json_decode($result);
        return $verification->success;
    }
}