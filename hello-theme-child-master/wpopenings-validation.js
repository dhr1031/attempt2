jQuery(document).ready(function($) {
    // Custom validation function
    function validateForm() {
        var nameField = $('#awsm-applicant-name').val();
        var textField = "Last";
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        console.log('Name Field:', nameField);
        console.log('Text Field:', textField);

        if (emailPattern.test(nameField) || emailPattern.test(textField)) {
            var errorMessage = $('<div class="custom-error" style="color: red;">The name field should not contain an email address.</div>');
            nameField.after(errorMessage);
           console.log('Form submission prevented');
            return false; // Ensure the form submission is stopped
        }

        console.log('Form validation passed');
        return true;
    }

    // Attach the validateForm function to your form's submit event
    $(document).on('submit', 'form[name="applicationform"]', function(event) {
        console.log('Form submit event triggered');
        var isValid = validateForm();
        if (!isValid) {
            console.log('Validation failed, form will not be submitted');
            event.preventDefault(); // Prevent form submission
            return false; // Stop form submission
        }
        console.log('Validation passed, form will be submitted');
    });
});