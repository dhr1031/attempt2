jQuery(document).ready(function($) {
    function validateForm() {
        var nameField = $('#awsm-applicant-name');
        var nameValue = nameField.val();
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        console.log('Name Field:', nameField);
        console.log('Name Value:', nameValue);

        $('.custom-error').remove(); // Remove existing error messages 015

        if (emailPattern.test(nameValue)) {
            console.log('Validation Error: The name field contains an email address.');
            var errorMessage = $('<div class="custom-error" style="color: red;">Name field invalid: email or URL.</div>');
            nameField.after(errorMessage);
            console.log('Form submission prevented');
            return false; // Ensure the form submission is stopped
        }

        console.log('Form validation passed');
        return true;
    }

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