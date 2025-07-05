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
                    location.reload();
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


$.validator.addMethod(
  "alphabetsOnly",
  function (value, element) {
    return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
  },
  "Please enter letters only"
);

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

$.validator.addMethod(
  "customEmail",
  function (value, element) {
    return (
      this.optional(element) ||
      /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)
    );
  },
  "Please enter a valid email address"
);