/**************************************************
  Coppermine 1.5.x Plugin - Picture Annotation (annotate)
  *************************************************
  Copyright (c) 2003-2009 Coppermine Dev Team
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.
  ********************************************
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
  **************************************************/

var loaded = false;

$(document).ready(function() {
    var alertTimerId = 0;
    $('#livesearch_input').keyup(function() {
        $('#livesearch_input').addClass('blue');
        clearTimeout(alertTimerId);
        alertTimerId = setTimeout(function () {
            $.post('index.php?file=annotate/reqserver', {livesearch:'1',q:$('#livesearch_input').val()}, function(data) { 
                $('#livesearch_output').html(data); 
                $('#livesearch_input').removeClass('blue');
            });
            loaded = true;
        }, 250);
    });

    /* create the Photo Note Container */
    container = document.getElementById('PhotoContainer');
    notes = new PhotoNoteContainer(container);
    for (n = 0; n < js_vars.annotations.length; n++) {
        /* create a note */
        var size = new PhotoNoteRect(js_vars.annotations[n].posx, js_vars.annotations[n].posy, js_vars.annotations[n].width, js_vars.annotations[n].height);
        var note = new PhotoNote(js_vars.annotations[n].note, 'note' + n, size, js_vars.annotations[n].user_name, js_vars.annotations[n].user_id);
        /* implement the save/delete functions */
        note.onsave = function (note) { return ajax_save(note); };
        note.ondelete = function (note) { return ajax_delete(note); };
        /* assign the note id number */
        note.nid = js_vars.annotations[n].nid;
        if (js_vars.visitor_annotate_permission_level < 3 && js_vars.annotations[n].user_id != js_vars.visitor_annotate_user_id) note.editable = false;
        /* add it to the container */
        notes.AddNote(note);
    }
    notes.HideAllNotes();
    addEvent(container, 'mouseover', function() { notes.ShowAllNotes(); });
    addEvent(container, 'mouseout', function() { notes.HideAllNotes(); });
});

function addnote(note_text){
    if (js_vars.visitor_annotate_permission_level < 2) {
        return false;
    }
    var newNote = new PhotoNote(note_text, 'note' + n, new PhotoNoteRect(10,10,50,50), '', '');
    newNote.onsave = function (note) { return ajax_save(note); };
    newNote.ondelete = function (note) { return ajax_delete(note); };
    notes.AddNote(newNote);
    newNote.Select();
    newNote.nid = 0;
    return false;
}

function ajax_save(note){
    var data = 'add=' + js_vars.pid + '&nid=' + note.nid + '&posx=' + note.rect.left + '&posy=' + note.rect.top + '&width=' + note.rect.width + '&height=' + note.rect.height + '&note=' + encodeURI(note.text);
    annotate_request(data, note);
    return true;
}

function ajax_delete(note){
    var data = 'remove=' + note.nid;
    annotate_request(data, note);
    return true;
}

function load_annotation_list() {
    if (loaded == false) {
        $('#livesearch_output').attr('disabled', 'disabled');
        $('#livesearch_output_loading').show();
        $.post('index.php?file=annotate/reqserver', { livesearch: '1', q: $('#livesearch_input').val() }, function(data) {
            $('#livesearch_output_loading').hide();
            $('#livesearch_output').html(data).removeAttr('disabled'); 
        });
        loaded = true;
    }
}

function annotate_request(data, note){

    if (window.XMLHttpRequest) { // Mozilla, Safari, ...
        var httpRequest = new XMLHttpRequest();
    } else if (window.ActiveXObject) { // IE
        try {
            var httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                var httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
    }

    httpRequest.onreadystatechange = function() { callback(httpRequest, note); };
    httpRequest.open('POST', 'index.php?file=annotate/reqserver', true);
    httpRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    httpRequest.send(data);

    return true;
}

function callback(req, note){

    if (req.readyState == 4) {
        if (req.status == 200) {
            note.nid = req.responseText;
        }
    }
}

function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;
    
    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";
    
    if(typeof(arr) == 'object') { //Array/Hashes/Objects 
        for(var item in arr) {
            var value = arr[item];
            
            if(typeof(value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}