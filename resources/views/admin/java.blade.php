<script src="{{ asset('admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('admin/assets/vendors/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendors/jvectormap/jquery-jvectormap.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ asset('admin/assets/vendors/owl-carousel-2/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('admin/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('admin/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('admin/assets/js/misc.js') }}"></script>
    <script src="{{ asset('admin/assets/js/settings.js') }}"></script>
    <script src="{{ asset('admin/assets/js/todolist.js') }}"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="{{ asset('admin/assets/js/dashboard.js') }}"></script>
    
    <!-- Simple and reliable dropdown functionality -->
    <script>
    // Simple dropdown toggle function
    function toggleUserDropdown() {
        console.log('Toggle function called');
        
        const menu = document.getElementById('userDropdownMenu');
        const toggle = document.getElementById('userDropdown');
        
        if (menu && toggle) {
            console.log('Found elements');
            
            // Check current display state
            const currentDisplay = window.getComputedStyle(menu).display;
            console.log('Current display:', currentDisplay);
            
            if (currentDisplay === 'none') {
                // Force the dropdown to be visible with multiple CSS properties
                menu.style.display = 'block';
                menu.style.visibility = 'visible';
                menu.style.opacity = '1';
                menu.style.transform = 'translateY(0)';
                menu.style.pointerEvents = 'auto';
                menu.classList.add('show');
                
                toggle.setAttribute('aria-expanded', 'true');
                toggle.classList.add('active');
                
                console.log('Dropdown opened with force');
                
                // Debug: Log the actual computed styles
                const computedStyle = window.getComputedStyle(menu);
                console.log('After opening - display:', computedStyle.display);
                console.log('After opening - visibility:', computedStyle.visibility);
                console.log('After opening - position:', computedStyle.position);
                console.log('After opening - z-index:', computedStyle.zIndex);
                console.log('After opening - top:', computedStyle.top);
                console.log('After opening - right:', computedStyle.right);
                
            } else {
                menu.style.display = 'none';
                menu.style.visibility = 'hidden';
                menu.style.opacity = '0';
                menu.classList.remove('show');
                
                toggle.setAttribute('aria-expanded', 'false');
                toggle.classList.remove('active');
                
                console.log('Dropdown closed');
            }
        } else {
            console.log('Elements not found - menu:', menu, 'toggle:', toggle);
        }
        
        return false;
    }
    
    // Document ready handler
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM ready, setting up dropdown...');
        
        const userDropdown = document.getElementById('userDropdown');
        const dropdownMenu = document.getElementById('userDropdownMenu');
        
        if (userDropdown && dropdownMenu) {
            console.log('Elements found in DOM ready');
            
            // Force initial styles
            dropdownMenu.style.display = 'none';
            dropdownMenu.style.visibility = 'hidden';
            dropdownMenu.style.opacity = '0';
            
            // Remove any existing event listeners first
            userDropdown.onclick = null;
            
            // Add click event listener
            userDropdown.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Click event triggered');
                toggleUserDropdown();
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.style.display = 'none';
                    dropdownMenu.style.visibility = 'hidden';
                    dropdownMenu.style.opacity = '0';
                    dropdownMenu.classList.remove('show');
                    userDropdown.setAttribute('aria-expanded', 'false');
                    userDropdown.classList.remove('active');
                    console.log('Dropdown closed by outside click');
                }
            });
            
            // Prevent dropdown from closing when clicking inside
            dropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            console.log('Dropdown setup complete');
        } else {
            console.log('Elements not found in DOM ready');
        }
    });
    
    // Emergency debug function - call this from console
    window.debugDropdown = function() {
        const menu = document.getElementById('userDropdownMenu');
        if (menu) {
            console.log('=== DROPDOWN DEBUG ===');
            const computed = window.getComputedStyle(menu);
            console.log('Element:', menu);
            console.log('Display:', computed.display);
            console.log('Visibility:', computed.visibility);
            console.log('Opacity:', computed.opacity);
            console.log('Position:', computed.position);
            console.log('Top:', computed.top);
            console.log('Right:', computed.right);
            console.log('Z-index:', computed.zIndex);
            console.log('Width:', computed.width);
            console.log('Height:', computed.height);
            console.log('Overflow:', computed.overflow);
            console.log('Transform:', computed.transform);
            
            // Force show for 5 seconds
            menu.style.display = 'block';
            menu.style.visibility = 'visible';
            menu.style.opacity = '1';
            menu.style.backgroundColor = 'red';
            menu.style.border = '5px solid blue';
            
            console.log('Forced visible with red background and blue border for 5 seconds');
            
            setTimeout(() => {
                menu.style.backgroundColor = 'white';
                menu.style.border = '2px solid #007bff';
            }, 5000);
        }
    };
    </script>

    <!-- PWA Integration Scripts -->
    <script src="/admin/assets/js/offline-manager.js"></script>
    
    <!-- PWA Install Prompt -->
// ...existing code...

<script>
    let deferredPrompt;
    let installButton = null;

    // Register Service Worker for PWA functionality
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw-advanced.js')
                .then(registration => {
                    console.log('✅ PWA Service Worker registered successfully:', registration);
                    
                    // Check for updates
                    registration.addEventListener('updatefound', () => {
                        console.log('🔄 PWA update found, new version available');
                    });
                })
                .catch(error => {
                    console.log('❌ PWA Service Worker registration failed:', error);
                });
        });
    }

    // Create install button
    function createInstallButton() {
        if (installButton) return;
        
        installButton = document.createElement('button');
        installButton.className = 'btn btn-sm btn-outline-primary me-2';
        installButton.innerHTML = '<i class="mdi mdi-download me-1"></i>Install App';
        installButton.style.display = 'none';
        
        installButton.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                deferredPrompt = null;
                installButton.style.display = 'none';
            }
        });
        
        // Add to navbar
        const navbar = document.querySelector('.navbar .navbar-nav');
        if (navbar) {
            const installContainer = document.createElement('li');
            installContainer.className = 'nav-item';
            installContainer.appendChild(installButton);
            navbar.insertBefore(installContainer, navbar.firstChild);
        }
    }

    // PWA Install Prompt
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('💡 PWA install prompt available');
        e.preventDefault();
        deferredPrompt = e;
        
        createInstallButton();
        if (installButton) {
            installButton.style.display = 'block';
        }
    });

    // PWA Installation Success
    window.addEventListener('appinstalled', () => {
        console.log('🎉 PWA was installed successfully');
        
        if (installButton) {
            installButton.style.display = 'none';
        }
        
        if (window.auditOfflineManager) {
            window.auditOfflineManager.showNotification('🎉 ERA Audit installed successfully!', 'success');
        }
    });

    // Check if already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        console.log('Running in standalone mode - PWA is installed');
    }
    </script>
// ...existing code...

    <!-- Enhanced Form Support for Offline -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add offline support to forms
            const forms = document.querySelectorAll('form[data-offline-support="true"]');
            
            forms.forEach(form => {
                // Auto-save functionality
                const formInputs = form.querySelectorAll('input, select, textarea');
                formInputs.forEach(input => {
                    input.addEventListener('input', debounce(() => {
                        if (!navigator.onLine && window.auditOfflineManager) {
                            const formData = new FormData(form);
                            const data = Object.fromEntries(formData.entries());
                            window.auditOfflineManager.saveDraft(data);
                        }
                    }, 2000));
                });

                // Handle form submission
                form.addEventListener('submit', async function(e) {
                    if (!navigator.onLine && window.auditOfflineManager) {
                        e.preventDefault();
                        
                        const formData = new FormData(form);
                        const data = Object.fromEntries(formData.entries());
                        
                        // Save response offline
                        if (data.question_id && data.audit_id) {
                            const result = await window.auditOfflineManager.saveResponseOffline(
                                data.audit_id,
                                data.question_id,
                                data.answer,
                                data.attachment_id || null
                            );
                            
                            if (result.success) {
                                // Clear form or show success message
                                form.reset();
                            }
                        }
                    }
                });
            });
        });

        // Debounce function for auto-save
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>

    <!-- PWA Scripts -->
    <script src="admin/assets/js/offline-manager.js"></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        // Register Service Worker for PWA functionality
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw-advanced.js')
                    .then(registration => {
                        console.log('✅ PWA Service Worker registered successfully:', registration);
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            console.log('🔄 PWA update found, new version available');
                        });
                    })
                    .catch(error => {
                        console.log('❌ PWA Service Worker registration failed:', error);
                    });
            });
        }

        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('💡 PWA install prompt available');
            e.preventDefault();
            deferredPrompt = e;
            
            // Show install button or banner
            showInstallButton();
        });

        function showInstallButton() {
            // Create install button if not exists
            if (!document.getElementById('pwa-install-btn')) {
                const installBtn = document.createElement('button');
                installBtn.id = 'pwa-install-btn';
                installBtn.className = 'btn btn-primary btn-sm position-fixed';
                installBtn.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; border-radius: 25px;';
                installBtn.innerHTML = '<i class="mdi mdi-download"></i> Install App';
                installBtn.onclick = installPWA;
                document.body.appendChild(installBtn);
            }
        }

        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('✅ User accepted PWA install');
                    } else {
                        console.log('❌ User dismissed PWA install');
                    }
                    deferredPrompt = null;
                    
                    // Hide install button
                    const installBtn = document.getElementById('pwa-install-btn');
                    if (installBtn) installBtn.remove();
                });
            }
        }

        // PWA Installation Success
        window.addEventListener('appinstalled', () => {
            console.log('🎉 PWA was installed successfully');
            
            // Hide install button
            const installBtn = document.getElementById('pwa-install-btn');
            if (installBtn) installBtn.remove();
            
            // Show success message
            if (window.auditOfflineManager) {
                window.auditOfflineManager.showNotification('🎉 ERA Audit installed successfully!', 'success');
            }
        });
    </script>