<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Prescription Dispensing<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <style>
        /* Main Content Layout */
        .main-content {
            padding: 20px;
            width: 100%;
            margin-left: 120px;
            transition: all 0.3s;
            background-color: #f8f9fa;
            min-height: calc(100vh - 56px);
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        /* Page Header */
        .page-header {
            background: #fff;
            padding: 15px 20px;
            margin: -20px -20px 20px -20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Card Styling */
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
            border: none;
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            color: #2c3e50;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
        }

        .btn i {
            margin-right: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        /* Layout Utilities */
        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        /* Cart Section */
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .empty-cart {
            text-align: center;
            color: #6c757d;
            padding: 20px;
            font-style: italic;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }

            .main-content.expanded {
                margin-left: 0;
            }
        }
    </style>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1>Prescription Dispensing</h1>
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-pills me-2"></i> Add Medication
                    </div>
                    <div class="card-body">
                        <form id="prescriptionForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="patient">Patient Name</label>
                                        <input type="text" id="patient" class="form-control" placeholder="Enter Patient Name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="doctor">Prescribing Doctor</label>
                                        <input type="text" id="doctor" class="form-control" placeholder="Enter Doctor's Name" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="medication">Medication</label>
                                        <select id="medication" class="form-control" required>
                                            <option value="">Select Medication</option>
                                            <!-- Medication options will be populated by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quantity">Quantity</label>
                                        <input type="number" id="quantity" class="form-control" min="1" value="1" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label for="notes">Notes / Instructions</label>
                                <textarea id="notes" class="form-control" rows="2" placeholder="Additional instructions"></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="button" id="addToCart" class="btn btn-primary">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart me-2"></i> Prescription Cart
                    </div>
                    <div class="card-body">
                        <div id="cartItems" class="mb-3">
                            <div class="empty-cart">No items added yet</div>
                        </div>
                        <div class="d-grid">
                            <button type="button" id="checkoutBtn" class="btn btn-primary" disabled>
                                <i class="fas fa-paper-plane"></i> Process Prescription
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (sidebar && mainContent) {
                const toggleSidebar = () => {
                    if (sidebar.classList.contains('closed')) {
                        mainContent.classList.add('expanded');
                    } else {
                        mainContent.classList.remove('expanded');
                    }
                };

                // Initial check
                toggleSidebar();

                // Add event listener for sidebar toggle
                const toggleBtn = document.querySelector('.toggle-btn');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', toggleSidebar);
                }
            }
        });

        // Your existing JavaScript code here
        $(document).ready(function() {
            let cart = [];
            
            // Sample medication data
            const medications = [
                { id: 'para', name: 'Paracetamol 500mg', category: 'Analgesics' },
                { id: 'ibu', name: 'Ibuprofen 200mg', category: 'Analgesics' },
                { id: 'amox', name: 'Amoxicillin 500mg', category: 'Antibiotics' },
                { id: 'loso', name: 'Losartan 50mg', category: 'Hypertension' },
                { id: 'meta', name: 'Metformin 500mg', category: 'Diabetes' },
                { id: 'sert', name: 'Sertraline 50mg', category: 'Antidepressants' }
            ];

            // Populate medication dropdown
            const $medicationSelect = $('#medication');
            const categories = [...new Set(medications.map(m => m.category))];
            
            categories.forEach(category => {
                const $optgroup = $(`<optgroup label="${category}"></optgroup>`);
                const categoryMeds = medications.filter(m => m.category === category);
                
                categoryMeds.forEach(med => {
                    $optgroup.append(`<option value="${med.id}">${med.name}</option>`);
                });
                
                $medicationSelect.append($optgroup);
            });

            // Add to cart functionality
            $('#addToCart').click(function() {
                const patient = $('#patient').val();
                const doctor = $('#doctor').val();
                const medicationId = $('#medication').val();
                const medicationName = $('#medication option:selected').text();
                const quantity = parseInt($('#quantity').val());
                const notes = $('#notes').val();
                
                if (!patient || !doctor || !medicationId) {
                    alert('Please fill in all required fields');
                    return;
                }
                
                // Add to cart
                cart.push({
                    id: Date.now(),
                    patient,
                    doctor,
                    medicationId,
                    medicationName,
                    quantity,
                    notes,
                    price: Math.floor(Math.random() * 100) + 10 // Random price for demo
                });
                
                // Update cart display
                updateCart();
                
                // Reset form
                $('#medication').val('');
                $('#quantity').val(1);
                $('#notes').val('');
            });

            // Remove item from cart
            $(document).on('click', '.remove-item', function() {
                const id = $(this).data('id');
                cart = cart.filter(item => item.id !== id);
                updateCart();
            });

            // Update quantity
            $(document).on('click', '.quantity-btn', function() {
                const id = $(this).data('id');
                const isIncrement = $(this).hasClass('increment');
                
                cart = cart.map(item => {
                    if (item.id === id) {
                        if (isIncrement) {
                            item.quantity++;
                        } else if (item.quantity > 1) {
                            item.quantity--;
                        }
                    }
                    return item;
                });
                
                updateCart();
            });

            // Update cart display
            function updateCart() {
                const $cartItems = $('#cartItems');
                
                if (cart.length === 0) {
                    $cartItems.html('<div class="empty-cart">No items added yet</div>');
                    $('#checkoutBtn').prop('disabled', true);
                    return;
                }
                
                let total = 0;
                let itemsHtml = '';
                
                cart.forEach(item => {
                    const itemTotal = item.quantity * item.price;
                    total += itemTotal;
                    
                    itemsHtml += `
                        <div class="cart-item">
                            <div class="cart-item-details">
                                <div><strong>${item.medicationName}</strong></div>
                                <div>Qty: ${item.quantity} x ₱${item.price.toFixed(2)}</div>
                                <div><small>${item.notes || 'No notes'}</small></div>
                            </div>
                            <div class="cart-item-actions">
                                <div class="quantity-controls">
                                    <button class="quantity-btn decrement" data-id="${item.id}">-</button>
                                    <span>${item.quantity}</span>
                                    <button class="quantity-btn increment" data-id="${item.id}">+</button>
                                </div>
                                <button class="remove-item" data-id="${item.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                itemsHtml += `
                    <div class="cart-total">
                        Total: ₱${total.toFixed(2)}
                    </div>
                `;
                
                $cartItems.html(itemsHtml);
                $('#checkoutBtn').prop('disabled', false);
            }

            // Checkout functionality
            $('#checkoutBtn').click(function() {
                if (cart.length === 0) return;
                
                // Here you would typically submit the form or make an AJAX call
                alert('Prescription processed successfully!');
                cart = [];
                updateCart();
                $('#prescriptionForm')[0].reset();
            });
        });
    </script>
<?= $this->endSection() ?>
