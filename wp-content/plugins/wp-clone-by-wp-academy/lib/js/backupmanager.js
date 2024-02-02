var wpclone_do_it_anyway = false;
jQuery(function($) {

    initialize();
    bindActions();
    getSize();
    scanBackupDir();
    uninstall();
    deleteFile();

    function initialize() {
        $("input[name$='backupChoice']").removeAttr('checked');
        checkCreateBackupOption();
    }

    function bindActions() {

        $("a#close-thickbox").click(function(e) {
            e.preventDefault();
            tb_remove();
            $("div#search-n-replace-info").html('');
            $("input[name='searchfor'], input[name='replacewith']").val('');
            $("input[name='ignoreprefix']").attr('checked', false);
        });

        $("input[name='search-n-replace-submit']").click(function(e) {
            e.preventDefault();
            if( $(this).data("working") ) return;
            $("#search-n-replace-info").html('').css( 'background', 'url(' + wpclone.spinner + ') no-repeat center' )
            $(this).data("working", true);
            search_and_replace();
        });

        $("input[id='fullBackup']").click(function() {

            $("#RestoreOptions").hide("fast");
            $("#file_directory").hide("fast");
            $("input[name$='createBackup']").attr('checked', true);
            $("input[name$='backupUrl']").removeAttr('checked');
            $("#backupChoices").show("fast");
            $("input#submit").val("Create Backup");

        });

        $("input[id='customBackup']").click(function() {

            $("#RestoreOptions").hide("fast");
            $("#file_directory").show("fast");
            $("input[name$='createBackup']").attr('checked', true);
            $("input[name$='backupUrl']").removeAttr('checked');
            $("#backupChoices").show("fast");
            $("input#submit").val("Create Backup");

        });

        $("input[name$='createBackup']").click(function() {

            $("#RestoreOptions").hide("fast");
            $("input[name$='backupUrl']").attr('checked', false);
            $("input[class$='restoreBackup']").each(function(){ $(this).attr('checked', false) });
            checkCreateBackupOption();

        });

        $("input[class$='restoreBackup']").click(function() {

            $("#RestoreOptions").show("fast");
            $("input[name$='backupUrl']").attr('checked', false);
            $(this).attr('checked', true);
            unCheckCreateBackupOption();
            $("input#submit").val("Restore Backup").removeClass("btn-primary").addClass("btn-warning");

        });

        $("input[name$='backupUrl']").click(function() {
            prepareBackUrlOption();
            $("input#submit").removeClass("btn-primary").addClass("btn-warning");
        });

        $("input[name$='restore_from_url']").focus(function() {
            prepareBackUrlOption();
            $("input#submit").removeClass("btn-primary").addClass("btn-warning");
        });

        $("input#submit").click(function(e) {

            if ($('#backupUrl').is(':checked')) {

                if ($("input[name$='restore_from_url']").val() == '') {
                    alert('Please enter the url you want to restore from.');
                } else if (!$('#approve').is(':checked')) {
                    alert('Please confirm that you agree to our terms by checking the "I AGREE" checkbox.');
                } else {
                    return getConfirmation('restore');
                }

                return false;

            } else if ($('input[class$="restoreBackup"]').is(':checked')) {

                if ($('#approve').is(':checked')) {
                    return getConfirmation('restore');
                }

                alert('Please confirm that you agree to our terms by checking "I AGREE (Required for "Restore" function):" checkbox.');
                return false;

            } else {

                console.log(wpclone_do_it_anyway);
                if (wpclone_do_it_anyway === true) {

                  wpclone_do_it_anyway = false;
                  return getConfirmation('create backup');

                } else {

                  e.preventDefault();
                  $('.wpclonse_pre_backup_modal').fadeIn(400);

                  return false;

                }

            }

            function getConfirmation(toDo) {
                return confirm('This may take a few minutes. Proceed to ' + toDo + ' now?');
            }
        });

        function unCheckCreateBackupOption() {
            $("input[name$='createBackup']").attr('checked', false);
            $("#backupChoices").hide("fast");
        }

        function prepareBackUrlOption() {
            $("#RestoreOptions").show("fast");
            $("input[name$='backupUrl']").attr('checked', true);
            $("input[class$='restoreBackup']").attr('checked', false);
            unCheckCreateBackupOption();
            $("input#submit").val('Restore from URL');
        }

    }

    function checkCreateBackupOption() {
        $("input[name$='createBackup']").attr('checked', true);
        $("#backupChoices").show("fast");
        $("input#submit").val("Create Backup").removeClass("btn-warning").addClass("btn-primary");
        $("input[id='fullBackup']").attr('checked',
        $("input[name$='createBackup']").is(':checked') && !$("input[id$='customBackup']").is(':checked'));
    }

    function getSize() {

        $.ajax({
            url: ajaxurl,
            type: 'get',
            data: {
                'action': 'wpclone-ajax-size',
                'nonce': wpclone.nonce
            },
            success: function(data){
                data = $.parseJSON(data);
                var cache = '';
                if( 'undefined' !== typeof data.time ) {
                    cache = '</br>(calculated ' + data.time + ' minute[s] ago.)';
                }
                $("span#filesize").html( "Number of files in wp-content directory - <code>" + data.files + "</code>, and their total size - <code>" + data.size + "</code> (files larger than 25MB will be excluded from the backup, you can change it from advanced settings) </br>Database size is <code>" + data.dbsize + "</code>." + cache );
            },
            error: function(e){
                $("span#filesize").html( "Unable to calculate size." );
            }
        });

    }

    function scanBackupDir() {

        $("a#dirscan").click( function(e){

            e.preventDefault();
            $(this).html("<img src='" + wpclone.spinner + "'>");

            $.ajax({
                url: ajaxurl,
                type: 'get',
                data: {
                    'action': 'wpclone-ajax-dir',
                    'nonce': wpclone.nonce
                },
                success: function(data){
                    window.location.reload(true);
                },
                error: function(e){
                }
            });


        });

    }

    function uninstall() {

        $("a#uninstall").click( function(e){

            e.preventDefault();
            if( ! confirm('This will delete all your backups files, are you sure?') ) return;
            $(this).html("<img src='" + wpclone.spinner + "'>");
            $.ajax({
                url: ajaxurl,
                type: 'get',
                data: {
                    'action': 'wpclone-ajax-uninstall',
                    'nonce': wpclone.nonce
                },
                success: function(data){
                    window.location.reload(true);
                },
                error: function(e){
                }
            });


        });

    }

    function deleteFile() {

        $("table.restore-backup-options a.delete").click( function(e){
            e.preventDefault();
            var row = $(this).closest("tr");
            var cell = $(this).closest("td");
            $(cell).html("<img src='" + wpclone.spinner + "'>");

            $.ajax({
                url: ajaxurl,
                type: 'get',
                data: {
                    'action': 'wpclone-ajax-delete',
                    'fileid': $(this).data("fileid"),
                    'nonce': wpclone.nonce
                },
                success: function(data){
                    data = $.parseJSON( data );

                    if( 'deleted' == data.status ) {
                        $(row).html("<td colspan='5'><strong>" + data.msg + "</strong></td>");
                        $(row).addClass('deleted').hide(700);
                    } else {
                        $(row).html("<td colspan='5'>" + data.msg + "</td>").addClass('delete-error');
                    }
                },
                error: function(e){
                }
            });

        });

    }

    function search_and_replace() {

        var prefix = '';

        if( $("input[name='ignoreprefix']").prop("checked") ) {
            prefix = 'true';
        }

        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action : 'wpclone-search-n-replace',
                search : $("input[name='searchfor']").val(),
                replace: $("input[name='replacewith']").val(),
                ignore_prefix : prefix,
                nonce  : wpclone.nonce
            },
            success: function(data) {
                $("div#search-n-replace-info").css('background', '').append(data);
            },
            error: function(e){
                console.log(e);
            },
            complete: function(){
                $("input[name='search-n-replace-submit']").removeData("working");
            }

        });

    }

    $("#banner-2-restore-close-icon").on("click", function (e) {
        $(".plugin-large-notice-restore-success").hide();
    })

    //Banner notice
    $(".banner-1 .close-icon").on("click", function (e) {
        $(".banner-1-collapsed").show(100);
        $(".banner-1").hide(100);

        $.ajax({
            url: ajaxurl,
            type: 'get',
            data: {
                'action': 'wpclone-ajax-banner1-close'
            },
            success: function(data){
                console.log(data);
            },
            error: function(e){
            }
        });
    })

    $(".banner-1-collapsed .remove-for-good").on("click", function (e) {
        $(".banner-1-collapsed").hide();

        $.ajax({
            url: ajaxurl,
            type: 'get',
            data: {
                'action': 'wpclone-ajax-banner1-removed'
            },
            success: function(data){
                console.log(data);
            },
            error: function(e){
            }
        });
    })
    $(".banner-1 .close-icon").on("click", function (e) {
        $(".banner-1-collapsed").show(100);
        $(".banner-1").hide(100);

        $.ajax({
            url: ajaxurl,
            type: 'get',
            data: {
                'action': 'wpclone-ajax-banner1-close'
            },
            success: function(data){
                console.log(data);
            },
            error: function(e){
            }
        });
    })

    $(".banner-1-collapsed .remove-for-good").on("click", function (e) {
        $(".banner-1-collapsed").hide();

        $.ajax({
            url: ajaxurl,
            type: 'get',
            data: {
                'action': 'wpclone-ajax-banner1-removed'
            },
            success: function(data){
                console.log(data);
            },
            error: function(e){
            }
        });
    });

    $('.bmplug__install-link').on('click', function () {
      $('.bmplug__install-link').css({'filter': 'grayscale(1)', 'pointer-events': 'none'});
      $('.bmplug__install-link').text('Installing...');
      $.post(ajaxurl, { action: 'wpclone-install_new' }).done((res) => {

        $('.bmplug__install-link').text('Redirecting...');
        if (isJsonString(res)) res = jsonParse(res);
        if (res.success === true) {

          setTimeout(function () { document.location.href = res.data.url; }, 500);

        } else {

          $('.bmplug__install-link').text('Installation failed...');
          setTimeout(function () { document.location.href = url; }, 500);

        }

      }).fail(() => {

        let url = $('.wpclonse_pre_backup_modal').data('url') + 'plugin-install.php?s=migrate&tab=search&type=author';
        $('.bmplug__install-link').text('Installation failed...');
        setTimeout(function () { document.location.href = url; }, 500);

      });
    });

    $('.modal__rejection-link').on('click', function (e) {
      $('.wpclonse_pre_backup_modal').fadeOut(300);
      wpclone_do_it_anyway = true;
      setTimeout(function () {
        HTMLFormElement.prototype.submit.call(document.querySelector('#backupForm'));
      }, 300);
    });

    $('.wpclone_close_modal_a').on('click', function () {
      wpclone_do_it_anyway = false;
      $('.wpclonse_pre_backup_modal').fadeOut(400);
    });

    // Is JSON String (helper)?
    function isJsonString(str) {
      try { JSON.parse(str); }
      catch (e) {
        if (typeof str === 'string') {
          let reversed = reverseJsonString(str);
          let lastcorrect = reversed.indexOf('}');
          if (lastcorrect == 0) lastcorrect = str.length;
          else lastcorrect = -lastcorrect;

          str = str.slice(str.indexOf('{'), lastcorrect);

          try {
            JSON.parse(str);
          } catch (e) {
            return false;
          }
          return true;
        } else return false;
      }
      return true;
    }

    // Reverse String (helper)
    function reverseJsonString(str) {
      if (typeof str === 'string')
        return (str === '') ? '' : reverseJsonString(str.substr(1)) + str.charAt(0);
      else
        return str;
    }

    // Parse JSON
    function jsonParse(str) {
      try { JSON.parse(str); }
      catch (e) {
        if (typeof str === 'string') {
          let reversed = reverseJsonString(str);
          let lastcorrect = reversed.indexOf('}');
          if (lastcorrect == 0) lastcorrect = str.length;
          else lastcorrect = -lastcorrect;
          str = str.slice(str.indexOf('{'), lastcorrect);
          try {
            JSON.parse(str);
          } catch (e) {
            return false;
          }
          return JSON.parse(str);
        } else return false;
      }
      return JSON.parse(str);
    }

});
