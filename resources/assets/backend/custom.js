//csrf token 
$(function() {
    $.ajaxSetup({
        headers: {      
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

$(document).ready(function() {
    //update status using ajax
    $(document).on('click', '.update-status', function() {
        var currentElement = $(this);
        var status = $(this).data('status');
        var id = $(this).data('id');
        var url = $(this).data('url');
        var method = $(this).data('method') || 'POST'; // Default to POST if method is not specified

        $.ajax({
            type: method,
            url: url,
            data: { id: id, status: status },
            success: function (response) {
                if (response.success) {
                    currentElement.tooltip("hide");
                    currentElement.tooltip("dispose");

                    currentElement.replaceWith(response.strHtml);

                    $('[data-toggle="tooltip"]').tooltip();

                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                console.error("Error updating status:", error);
                console.error("Status:", status);
                console.error("Response:", xhr.responseText);
                toastr.error(
                    "An error occurred while updating the status. please try again."
                );
            },
        });
    });

    //delete record using ajax with confirmation
    $(document).on('click', '.delete-record', function(e) {
        e.preventDefault();
        var currentElement = $(this);        
        var id = $(this).data('id');
        var url = $(this).data('url');
        var redirectUrl = $(this).data("redirect");
        var method = $(this).data('method') || 'POST'; // Default to POST if method is not specified
        var text = $(this).data('text') || "You won't be able to revert this!"; // Default text if not specified

        Swal.fire({
        //   title: "Are you sure?",
          text: text,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!",
          cancelButtonText: "No, cancel!",
          customClass: {
            confirmButton: "btn btn-outline-danger",
            cancelButton: "btn btn-outline-success",
          },
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              type: method,
              url: url,
              data: { id: id },
              success: function (response) {
                if (response.success) {
                  currentElement.closest("tr").remove();
                  toastr.success(response.message);
                  setTimeout(function () {
                      if (redirectUrl) {
                        window.location.href = redirectUrl;
                      } else {
                        location.reload();
                      }
                  }, 1000);
                } else {
                  toastr.error(response.message);
                }
              },
              error: function (xhr, status, error) {
                console.error("Error deleting record:", error);
                console.error("Status:", status);
                console.error("Response:", xhr.responseText);
                toastr.error(
                  "An error occurred while deleting the record. please try again."
                );
              },
            });
          }
        });
    });
});

$(document).ready(function () {
  $(".numbers-only").on("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");
  });
});
$(document).ready(function () {
  $(".alphabets-only").on("input", function () {
    this.value = this.value.replace(/[^a-zA-Z\s]/g, "");
  });
});

//dynamic global modal implementation
$(document).on('click', '.open-dynamic-modal', function () {
  const config = $(this).data('config') || {};

  $('#globalModalTitle').text(config.title || 'Modal Title');
  $('#globalDynamicForm').attr('action', config.action_url || '#');
  $('#globalModalBody').html(config.body_html || '<p>No content</p>');

  // If Select2 is needed
  if (config.init_select2) {
      $('#globalModalBody').find('select.select2').select2({
          dropdownParent: $('#globalDynamicModal'),
          placeholder: config.placeholder || 'Select',
          width: '100%',
          ajax: config.ajax_url ? {
              url: config.ajax_url,
              dataType: 'json',
              delay: 250,
              processResults: function (data) {
                  return {
                      results: data
                  };
              },
              cache: true
          } : undefined
      });
  }

  $('#globalDynamicModal').modal('show');
});