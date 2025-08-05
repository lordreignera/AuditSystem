<!-- Test dropdown functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Testing dropdown functionality...');
    
    // Check if Bootstrap is loaded
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap is loaded:', bootstrap.VERSION);
    } else {
        console.log('Bootstrap is NOT loaded');
    }
    
    // Find the user dropdown
    const userDropdown = document.getElementById('userDropdown');
    if (userDropdown) {
        console.log('User dropdown found:', userDropdown);
        
        // Check if dropdown is properly initialized
        const dropdownInstance = bootstrap.Dropdown.getInstance(userDropdown);
        if (dropdownInstance) {
            console.log('Dropdown instance exists');
        } else {
            console.log('Dropdown instance does not exist, creating one...');
            try {
                new bootstrap.Dropdown(userDropdown);
                console.log('Dropdown instance created successfully');
            } catch (error) {
                console.error('Error creating dropdown:', error);
            }
        }
        
        // Add click event listener for debugging
        userDropdown.addEventListener('click', function(e) {
            console.log('Dropdown clicked', e);
        });
    } else {
        console.log('User dropdown NOT found');
    }
});
</script>
