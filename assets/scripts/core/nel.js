var nelliel = {};
nelliel.setup = {};
nelliel.core = {};
nelliel.events = {};
nelliel.posting_form = {};
nelliel.ui = {};

function dataBin() {
    ;
}

nelliel.setup.doImportantStuff = function(board_id, is_modmode) {
    dataBin.board_id = board_id;
    dataBin.is_modmode = is_modmode;
    dataBin.hidden_threads_id = "hidden_threads_" + board_id;
    dataBin.hidden_posts_id = "hidden_posts_" + board_id;
    nelliel.setup.localStorageInitCheck();
    dataBin.hidden_threads = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_threads_id, true);
    dataBin.hidden_posts = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_posts_id, true);
    dataBin.collapsedThreads = [];

    if (board_id === "") {
        setStyle(nelliel.core.getCookie("base-style"));
    }
    else {
        setStyle(nelliel.core.getCookie("style-" + board_id));
    }

    nelliel.setup.setupListeners();
    nelliel.core.hashHandler();
    
    if (board_id !== "") {
        nelliel.setup.fillForms(board_id);
        nelliel.ui.applyHidePostThread();
    }
    
    nelliel.core.unhideJSonly();
}

nelliel.setup.localStorageInitCheck = function() {
    if (!localStorage[dataBin.hidden_threads_id]) {
        localStorage[dataBin.hidden_threads_id] = '{}';
    }
    
    if (!localStorage[dataBin.hidden_posts_id]) {
        localStorage[dataBin.hidden_posts_id] = '{}';
    }
}

nelliel.setup.setupListeners = function() {
    var jsevents_elements = document.querySelectorAll("[data-jsevents]");
    var elements_length = jsevents_elements.length;

    for (var i = 0; i < elements_length; i++) {
        var events_list = jsevents_elements[i].getAttribute("data-jsevents").split("|");
        var list_length = events_list.length;

        for (var j = 0; j < list_length; j++) {
            if (events_list[j].includes("click")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "click", nelliel.events.processPostClicks);
            }
            
            if (events_list[j].includes("mouseover")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "mouseover", nelliel.events.processMouseOver);
            }
            
            if (events_list[j].includes("mouseout")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "mouseout", nelliel.events.processMouseOut);
            }
            
            if (events_list[j].includes("change")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "change", nelliel.events.processChanges);
            }
        }
    }

    window.addEventListener("hashchange", nelliel.hashHandler);
}

nelliel.setup.addListenerIfElementExists = function(element, event, event_handler) {
    if (element !== null) {
        element.addEventListener(event, event_handler);
    }
}

nelliel.setup.fillForms = function(board) {
    var pwd = nelliel.core.getCookie("pwd-" + board);
    var name = nelliel.core.getCookie("name-" + board);
    
    if (pwd !== null) {
        if (document.getElementById("posting-form-sekrit") !== null) {
            document.getElementById("posting-form-sekrit").value = pwd;
        }

        if (document.getElementById("update-sekrit") !== null) {
            document.getElementById("update-sekrit").value = pwd;
        }
    }

    /* document.getElementById("not-anonymous").value = name; */
}

nelliel.events.processPostClicks = function(event) {
    if (event.target.hasAttribute("data-command")) {
        if (event.target.hasAttribute("data-content-id")) {
            var content_id = nelliel.core.contentID(event.target.getAttribute("data-content-id"))
        }

        var command = event.target.getAttribute("data-command");

        if (command === "expand-thread" || command === "collapse-thread") {
            nelliel.ui.expandCollapseThread(event.target, command);
        } else if (command === "expand-thread-render" || command === "collapse-thread-render") {
            nelliel.ui.expandCollapseThread(event.target, command, true);
        } else if (command === "link-post") {
            nelliel.ui.linkPost(event.target);
        } else if (command === "show-file-meta" || command === "hide-file-meta") {
            nelliel.ui.showHideFileMeta(event.target);
        } else if (command === "add-file-meta") {
            addNewFileMeta(event.target, command);
        } else if (command === "inline-expand" || command === "inline-reduce") {
            nelliel.ui.inlineExpandReduce(event.target, command);
            event.preventDefault();
        } else if (command === "hide-post" || command === "show-post" ) {
            nelliel.ui.hideShowPost(event.target, command);
        } else if (command === "hide-thread" || command === "show-thread" ) {
            nelliel.ui.hideShowThread(event.target, command);
        } else if (command === "reload-captcha" ) {
            reloadCAPTCHA(event.target, command);
        }

        if (event.target.hasAttribute("href") && event.target.getAttribute("href").match(/^#$/) !== null) {
            event.preventDefault();
        }
    }
}

nelliel.events.processMouseOver = function(event) {
    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "show-linked-post") {
            nelliel.ui.showLinkedPost(event.target, event);
        }
    }
}

nelliel.events.processMouseOut = function(event) {
    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "show-linked-post") {
            nelliel.ui.hideLinkedPost(event.target, event);
        }
    }
}

nelliel.events.processChanges = function(event) {
    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "reveal-file-input") {
            nelliel.posting_form.showNextFileInput(event.target);
        } else if (command === "change-style") {
            setStyle(event.target.value, true);
        }
    }
}

nelliel.core.setCookie = function(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString())
            + ";path=/";
}

nelliel.core.getCookie = function(key) {
    var csplit = document.cookie.split('; ');

    for (var i = 0; i < csplit.length; i++) {
        var s2 = csplit[i].split('=');
        if (s2[0] == key) {
            return s2[1];
        }
    }

    return null;
}

nelliel.core.storeInLocalStorage = function(data_path, data) {
    data = (typeof data !== "string") ? JSON.stringify(data) : data;
    localStorage[data_path] = data;
}

nelliel.core.storeInSessionStorage = function(data_path, data) {
    data = (typeof data !== "string") ? JSON.stringify(data) : data;
    localStorage[data_path] = data;
}

nelliel.core.retrieveFromLocalStorage = function(data_path, parse) {
    parse = (typeof parse === "undefined") ? false : parse;
    data = localStorage[data_path];
    data = (typeof data !== "undefined" && parse === true) ? JSON.parse(data) : data;
    return data;
}

nelliel.core.retrieveFromSessionStorage = function(data_path, parse) {
    parse = (typeof parse === "undefined") ? false : parse;
    data = sessionStorage[data_path];
    data = (typeof data !== "undefined" && parse === true) ? JSON.parse(data) : data;
    return data;
}

nelliel.posting_form.showNextFileInput = function (element) {
    var file_num = element.id.replace("up-file-", "");
    file_num++;
    var next_file = document.getElementById("form-file-" + file_num);
    next_file.className = next_file.className.replace(/\bhidden\b/g, "");
}

nelliel.core.hashHandler = function () {
    var hash_match = location.hash.match(/#t([0-9]+)p([0-9]+)/);
    
    if (hash_match !== null) {
        var content_id = nelliel.core.contentID('cid_' + hash_match[1] + '_' + hash_match[2] + '_0');
        
        if (hash_match[2] > 1) {
            nelliel.ui.highlightPost(content_id);
        }
    }
}

nelliel.core.contentID = function (id_string) {
    var content_id = {};
    var segments = id_string.split('_');
    content_id.id_string = id_string;
    content_id.thread_id = segments[1];
    content_id.post_id = segments[2];
    content_id.content_order = segments[3];
    return content_id;
}

nelliel.core.unhideJSonly = function () {
    var elements = document.querySelectorAll("[data-jsonly]");
    var element_count = elements.length;
    
    for (i = 0; i < element_count; i++) {
        nelliel.ui.toggleHidden(elements[i]);
    }
}

function addNewFileMeta(element, command) {
    var target_id = element.id.replace("add-", "form-");
    var target_element = document.getElementById(target_id);
    target_element.className = target_element.className.replace(/\bhidden\b/g, "");
    element.className += " hidden";
}

function setStyle(style, update_cookie = false) {
    if (style == null) {
        return;
    }
    
    if (update_cookie) {
        if (dataBin.board_id == "") {
            style_cookie = "base-style";
        } else {
            style_cookie = "style-" + dataBin.board_id;
        }
        
        nelliel.core.setCookie(style_cookie, style, 9001);
    }


    var allstyles = document.getElementsByTagName("link");

    for (i = 0; i < allstyles.length; i++) {
        allstyles[i].disabled = true;

        if (allstyles[i].getAttribute("data-style-type") == "style-board") {
            if (allstyles[i].getAttribute("data-id") == style) {
                allstyles[i].disabled = false;
            }
        }
    }

    var style_menus = document.getElementsByClassName("styles-menu");
    
    for (i = 0; i < style_menus.length; i++) {
        style_menus.item(i).value = style;
      } 
}

function reloadCAPTCHA(event_target, command) {
    var regen_url = event_target.getAttribute("data-url");
    var request = new XMLHttpRequest();
    request.open('GET', regen_url);
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            var captchas = document.getElementsByClassName("captcha-image", document);
            var captcha_count = captchas.length;

            for (i = 0; i < captcha_count; i++) {
                var original_url = captchas[i].getAttribute("src");
                
                if (original_url.includes("&time=")) {
                    var new_image_url = captchas[i].getAttribute("src").replace(/&time=[0-9]*/, "&time=" + Date.now());
                } else {
                    var new_image_url = captchas[i].getAttribute("src") + "&time=" + Date.now();
                }

                captchas[i].setAttribute("src", new_image_url);
            }
        }
    };
    
    request.send();
}