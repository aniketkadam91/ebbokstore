(function($) {
  $(document).ready(function() {
    var yaysmtp_startTime_picker = moment().subtract(6, "days");
    var yaysmtp_endTime_picker = moment();
    var yay_smtp_char_obj = "";

    var yaysmtp_export_mail_log_startTime_picker = moment();
    var yaysmtp_export_mail_log_endTime_picker = moment();

    function yaysmtp_chart(fromDate, toDate) {
      if (yay_smtp_char_obj instanceof Chart) {
        yay_smtp_char_obj.destroy();
      }
      $.ajax({
        url: yaySmtpWpData.YAY_ADMIN_AJAX,
        type: "POST",
        data: {
          action: "yaysmtp_overview_chart",
          nonce: yaySmtpWpData.ajaxNonce,
          params: {
            from: fromDate.format("YYYY-MM-DD"),
            to: toDate.format("YYYY-MM-DD")
          }
        },
        beforeSend: function() {
          yaySMTPspinner("yaysmtp-analytics-email-wrap", true);
        },
        success: function(result) {
          if (result.success) {
            let data = result.data.data;
            const yaysmtp_labels = data.labels;
            const yaysmtp_datasets = data.datasets;
            const yaysmtp_data = {
              labels: yaysmtp_labels,
              datasets: yaysmtp_datasets
            };
            const yaysmtpConfig = {
              type: "line",
              data: yaysmtp_data,
              options: {
                responsive: true,
                ticks: {
                  precision: 0
                },
                plugins: {
                  legend: {
                    position: "top",
                    display: false
                  },
                  tooltip: {
                    multiKeyBackground: "#00000000",
                    callbacks: {
                      labelColor: function(context) {
                        return {
                          backgroundColor: context.dataset.backgroundColor
                        };
                      }
                    }
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true
                  }
                }
              }
            };

            let totalMail =
              parseInt(data.successTotal) + parseInt(data.failTotal);
            if (
              $("body").find(".yaysmtp-chart-sumary .total-mail").length > 0
            ) {
              $("body")
                .find(".yaysmtp-chart-sumary .total-mail")
                .html(totalMail);
            }

            if ($("body").find(".yaysmtp-chart-sumary .sent-mail").length > 0) {
              $("body")
                .find(".yaysmtp-chart-sumary .sent-mail")
                .html(data.successTotal);
            }

            if (
              $("body").find(".yaysmtp-chart-sumary .failed-mail").length > 0
            ) {
              $("body")
                .find(".yaysmtp-chart-sumary .failed-mail")
                .html(data.failTotal);
            }

            if ($("body").find("#yaysmtpCharts").length > 0) {
              var yaysmtpctx = document
                .getElementById("yaysmtpCharts")
                .getContext("2d");

              yay_smtp_char_obj = new Chart(yaysmtpctx, yaysmtpConfig);
            }

            // Top mail List
            render_top_mail_list_html(data.topMailList);
          }
          yaySMTPspinner("yaysmtp-analytics-email-wrap", false);
        }
      });
    }

    if( 
      window.location.href === yaySmtpWpData.DASHBOARD_URL 
      || window.location.href === yaySmtpWpData.DASHBOARD_URL + 'index.php'
    ) {
      yaysmtp_input_daterangepicker(
        yaysmtp_startTime_picker,
        yaysmtp_endTime_picker
      );
    }

    if ($("body").find("#yaysmtp_daterangepicker").length > 0) {
      $("#yaysmtp_daterangepicker").daterangepicker(
        {
          startDate: yaysmtp_startTime_picker,
          endDate: yaysmtp_endTime_picker,
          alwaysShowCalendars: true,
          showCustomRangeLabel: false,
          autoUpdateInput: false,
          ranges: {
            "Last 7 Days": [moment().subtract(6, "days"), moment()],
            "This Month": [moment().startOf("month"), moment().endOf("month")],
            "Last 3 Months": [moment().subtract(90, "days"), moment()]
          },
          locale: {
            cancelLabel: "Reset",
            format: "YYYY-MM-DD"
          }
        },
        yaysmtp_input_daterangepicker
      );
    }

    $("body")
      .find("#yaysmtp_daterangepicker")
      .on("cancel.daterangepicker", function(ev, picker) {
        $("#yaysmtp_daterangepicker")
          .data("daterangepicker")
          .setStartDate(yaysmtp_startTime_picker.format("YYYY/MM/DD"));
        $("#yaysmtp_daterangepicker")
          .data("daterangepicker")
          .setEndDate(yaysmtp_endTime_picker.format("YYYY/MM/DD"));

        yaysmtp_input_daterangepicker(
          yaysmtp_startTime_picker,
          yaysmtp_endTime_picker
        );
      });

    if ($("body").find("#yaysmtp_daterangepicker_export_mail_logs").length > 0) {
      $("#yaysmtp_daterangepicker_export_mail_logs").daterangepicker(
        {
          startDate: yaysmtp_export_mail_log_startTime_picker,
          endDate: yaysmtp_export_mail_log_endTime_picker,
          alwaysShowCalendars: true,
          showCustomRangeLabel: false,
          autoUpdateInput: false,
          ranges: {
            Today: [moment(), moment()],
            // "Last 7 Days": [moment().subtract(6, "days"), moment()],
            "This Month": [moment().startOf("month"), moment().endOf("month")],
            "Last Month": [
              moment()
                .subtract(1, "month")
                .startOf("month"),
              moment()
                .subtract(1, "month")
                .endOf("month")
            ],
            // "Last 6 Months": [moment().subtract(180, "days"), moment()],
            "This Year": [moment().startOf("year"), moment()]
          },
          locale: {
            cancelLabel: "Cancel",
            format: "YYYY-MM-DD"
          }
        },
        yaysmtp_input_export_email_log_daterangepicker
      );
    }
    
    $("body")
      .find("#yaysmtp_daterangepicker_export_mail_logs")
      .on("cancel.daterangepicker", function(ev, picker) {
        let startDate = moment();
        let endDate = moment();

        $("#yaysmtp_daterangepicker_export_mail_logs")
          .data("daterangepicker")
          .setStartDate(startDate.format("YYYY/MM/DD"));
        $("#yaysmtp_daterangepicker_export_mail_logs")
          .data("daterangepicker")
          .setEndDate(endDate.format("YYYY/MM/DD"));

          yaysmtp_input_export_email_log_daterangepicker("", "");
      });

    function yaysmtp_input_daterangepicker(start, end) {
      $("#yaysmtp_daterangepicker").val(
        start.format("YYYY/MM/DD") + " - " + end.format("YYYY/MM/DD")
      );

      yaysmtp_chart(start, end);
    }

    function yaysmtp_input_export_email_log_daterangepicker(start, end) {
      if( start && end ) {
        $("#yaysmtp_daterangepicker_export_mail_logs").val(
          start.format("YYYY/MM/DD") + " - " + end.format("YYYY/MM/DD")
        );
      } else {
        $("#yaysmtp_daterangepicker_export_mail_logs").val("");
      }
     
      yaysmtp_export_mail_log_startTime_picker = start ? start : moment();
      yaysmtp_export_mail_log_endTime_picker   = end ? end : moment();
    }

    function render_top_mail_list_html(topMailList) {
      $(
        ".yaysmtp-analytics-email-wrap .top-mail-table-wrap .top-mail-body"
      ).html("");

      let html = "";
      if (topMailList.length > 0) {
        topMailList.forEach(function(item) {
          html += "<tr>";
          html += '<td class="table-item">' + item.title + "</td>";
          html +=
            '<td class="table-item">' +
            (parseInt(item.sent) + parseInt(item.failed)) +
            "</td>";
          html += '<td class="table-item">' + item.sent + "</td>";
          html += '<td class="table-item">' + item.failed + "</td>";
          html += "</tr>";
        });
      }
      if (html != "") {
        $(
          ".yaysmtp-analytics-email-wrap .top-mail-table-wrap .top-mail-body"
        ).html(html);
        $(
          ".yaysmtp-analytics-email-wrap .top-mail-table-wrap .top-mail-table"
        ).show();
        $(
          ".yaysmtp-analytics-email-wrap .top-mail-table-wrap .top-mail-table-empty"
        ).hide();
      } else {
        $(
          ".yaysmtp-analytics-email-wrap .top-mail-table-wrap .top-mail-table"
        ).hide();
        $(
          ".yaysmtp-analytics-email-wrap .top-mail-table-wrap .top-mail-table-empty"
        ).show();
      }
    }

    $("body").on(
      "click",
      ".yaysmtp-import-settings-notice .close-btn",
      function() {
        $(".yaysmtp-import-settings-notice").remove();
        $.ajax({
          url: yaySmtpWpData.YAY_ADMIN_AJAX,
          type: "POST",
          data: {
            action: "yaysmtp_close_popup_import_smtp_settings",
            nonce: yaySmtpWpData.ajaxNonce
          },
          success: function(result) {}
        });
      }
    );

    $(".yaysmtp-import-settings-btn").prop(
      "disabled",
      true
    );

    $(".yay-smtper-plugin").click(function() {
      $(this).addClass("active");
      let pluginEls = $(this).siblings(".yay-smtper-plugin");
      $.each(pluginEls, function() {
        $(this).removeClass("active");
      });

      let pluginName = $(this).attr("data-plugin");
      $(".yaysmtp-import-plugin-choose").val(pluginName);

      $(".yaysmtp-import-settings-btn").prop(
        "disabled",
        false
      );
    });

    $(".yaysmtp-import-settings-btn").click(
      function() {
        let pluginName = $(".yaysmtp-import-plugin-choose").val();
        if ("" != pluginName) {
          $.ajax({
            url: yaySmtpWpData.YAY_ADMIN_AJAX,
            type: "POST",
            data: {
              action: "yaysmtp_import_smtp_settings",
              nonce: yaySmtpWpData.ajaxNonce,
              plugin_name: pluginName
            },
            beforeSend: function() {
              yaySMTPspinner("yay-smtp-wrap", true);
            },
            success: function(result) {
              yaySMTPNotification(result.data.mess, "yay-smtp-wrap", true);
              yaySMTPspinner("yay-smtp-wrap", false);
            }
          });
        }
      }
    );

    $(".yaysmtp-export-email-log-btn").click(
      function() {
        let fieldsDisplay = [];
        const fieldInputs = $('.yaysmtp-export-log-general-field-input, .yaysmtp-export-log-additional-field-input');
        $.each(fieldInputs, function() { 
          if ($(this).is(":checked")) {
            const fieldDisplay = $(this).val();
            fieldsDisplay.push(fieldDisplay);
          }
        });
     
        let from = "";
        let to   = "";
        if( $('#yaysmtp_daterangepicker_export_mail_logs').val() ) {
          from = yaysmtp_export_mail_log_startTime_picker.format("YYYY-MM-DD");
          to   = yaysmtp_export_mail_log_endTime_picker.format("YYYY-MM-DD");
        }

        let searchKey   = $('.yaysmtp-export-log-search-key').val();
        let searchValue = $('.yaysmtp-export-log-search-value').val();

        const paramValues = {
          'fieldsDisplay' : fieldsDisplay,
          'from'          : from,
          'to'            : to,
          'searchKey'     : searchKey,
          'searchValue'   : searchValue
        }

        $.ajax({
          url: yaySmtpWpData.YAY_ADMIN_AJAX,
          type: "POST",
          data: {
            action: "yaysmtp_export_email_log",
            nonce: yaySmtpWpData.ajaxNonce,
            params: paramValues
          },
          beforeSend: function() {
            yaySMTPspinner("yay-smtp-wrap", true);
          },
          success: function(response) {
            if( response ) {
              if( typeof response === 'object' && ! response.success ) {
                yaySMTPNotification( response.data.mess, "yay-smtp-wrap", false);
              } else {
                var blob = new Blob( [ response ], { type: 'application/octetstream' } );

                var a = document.createElement( 'a' );
                a.href = window.URL.createObjectURL( blob );
                a.download = 'yaysmtp-email-log-' + moment().format("YYYYMMDDHHmmss") + '.csv';
        
                document.body.appendChild( a );
                a.click();
                document.body.removeChild( a );
                window.URL.revokeObjectURL( a.href );
  
                yaySMTPNotification("Export email logs successful.", "yay-smtp-wrap", true);
              }
            } else {
              yaySMTPNotification('Data is empty.', "yay-smtp-wrap", false);
            }
            
            yaySMTPspinner("yay-smtp-wrap", false);
          }
        });

      }
    );
  });
})(window.jQuery);
