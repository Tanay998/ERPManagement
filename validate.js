document.addEventListener('DOMContentLoaded', function() {
    // Tab configuration with required fields
    const tabs = [
        { 
            id: 'home', 
            name: 'Admission Details', 
            fields: ['dateadd', 'regNo', 'entry', 'course', 'semester'] 
        },
        { 
            id: 'menu1', 
            name: 'Personal Details I', 
            fields: ['firstname', 'lastname', 'fathername', 'phone', 'dateone', 'Email'] 
        },
        { 
            id: 'menu2', 
            name: 'Personal Details II', 
            fields: ['phone1', 'gender', 'category', 'marital', 'blood', 'add1'] 
        },
        { 
            id: 'menu3', 
            name: 'Personal Details III', 
            fields: ['aadhaar', 'ccity', 'cstreet', 'cstate', 'cpincode'] 
        },
        { 
            id: 'menu4', 
            name: 'Education and Bank Details', 
            fields: ['bankacc', 'IFSC', 'BANKNAME', 'edu', 'schoolName', 'yop'] 
        },
        { 
            id: 'menu5', 
            name: 'Upload Documents I', 
            fields: ['file'] 
        }
    ];

    // Form submission handler
    document.getElementById('form').addEventListener('submit', function(e) {
        if (!validateAllTabs()) {
            e.preventDefault();
        }
    });

    // Validate all tabs
    function validateAllTabs() {
        let firstInvalidTab = null;
        let isValid = true;

        for (const tab of tabs) {
            let tabIsValid = true;
            
            for (const fieldId of tab.fields) {
                const field = document.getElementById(fieldId);
                if (field && !validateField(field)) {
                    tabIsValid = false;
                    isValid = false;
                    
                    if (!firstInvalidTab) {
                        firstInvalidTab = tab;
                    }
                }
            }
            
            // Highlight invalid tabs
            const tabLink = document.querySelector(`.nav-link[href="#${tab.id}"]`);
            if (tabLink) {
                if (!tabIsValid) {
                    tabLink.classList.add('text-danger');
                    const asterisk = document.createElement('span');
                    asterisk.className = 'text-danger';
                    asterisk.textContent = ' *';
                    tabLink.appendChild(asterisk);
                } else {
                    tabLink.classList.remove('text-danger');
                    const asterisk = tabLink.querySelector('.text-danger');
                    if (asterisk) asterisk.remove();
                }
            }
        }

        if (!isValid && firstInvalidTab) {
            activateTab(firstInvalidTab.id);
            const firstInvalidField = document.getElementById(firstInvalidTab.fields.find(id => {
                const field = document.getElementById(id);
                return field && !validateField(field);
            }));
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalidField.focus();
            }
            alert(`Please complete all required fields in the ${firstInvalidTab.name} tab`);
            return false;
        }

        return true;
    }

    // Validate individual field
    function validateField(field) {
        if (!field) return true;
        
        const value = field.value.trim();
        let isValid = true;
        
        // Clear previous errors
        clearFieldError(field);
        
        // Check required fields
        if (field.required && (value === '' || value === null)) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
        
        // Field-specific validation
        else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
        
        else if (field.type === 'tel' && !/^\d{10}$/.test(value)) {
            showFieldError(field, 'Please enter a 10-digit phone number');
            isValid = false;
        }
        
        else if (field.type === 'select-one' && field.selectedIndex <= 0) {
            showFieldError(field, 'Please select an option');
            isValid = false;
        }
        
        else if (field.type === 'file' && !field.files.length) {
            showFieldError(field, 'Please upload a file');
            isValid = false;
        }
        
        return isValid;
    }

    // Show field error
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let errorElement = field.nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
            errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback';
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        }
        errorElement.textContent = message;
    }

    // Clear field error
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('invalid-feedback')) {
            errorElement.remove();
        }
    }

    // Activate tab
    function activateTab(tabId) {
        // Remove active class from all tabs
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Add active class to target tab
        const tabLink = document.querySelector(`.nav-link[href="#${tabId}"]`);
        const tabPane = document.getElementById(tabId);
        
        if (tabLink && tabPane) {
            tabLink.classList.add('active');
            tabPane.classList.add('show', 'active');
        }
    }

    // Initialize tab navigation
    function initTabNavigation() {
        $('.nexttab').on('click', function() {
            const currentTab = $('.nav-link.active');
            const nextTab = currentTab.parent().next().find('.nav-link');
            if (nextTab.length) {
                currentTab.removeClass('active');
                nextTab.addClass('active');
                $(currentTab.attr('href')).removeClass('show active');
                $(nextTab.attr('href')).addClass('show active');
            }
        });

        $('.prevtab').on('click', function() {
            const currentTab = $('.nav-link.active');
            const prevTab = currentTab.parent().prev().find('.nav-link');
            if (prevTab.length) {
                currentTab.removeClass('active');
                prevTab.addClass('active');
                $(currentTab.attr('href')).removeClass('show active');
                $(prevTab.attr('href')).addClass('show active');
            }
        });
    }

    // Initialize
    initTabNavigation();
});