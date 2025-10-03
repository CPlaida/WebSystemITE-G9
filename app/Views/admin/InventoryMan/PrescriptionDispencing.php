<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('title') ?>Prescription Dispensing<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* Main Layout */
    .prescription-container {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }
    
    .prescription-main {
        flex: 2;
        min-width: 0;
    }
    
    .prescription-sidebar {
        flex: 1;
        min-width: 350px;
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
        font-size: 1rem;
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
        font-size: 0.9rem;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        transition: border-color 0.15s ease-in-out;
    }
    
    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        font-weight: 500;
        border-radius: 7px;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        border: 1px solid transparent;
    }
    
    .btn i {
        margin-right: 5px;
    }
    
    .btn-primary {
        background-color: #007bff;
        color: #fff;
        
    }
    
    .btn-block {
        display: block;
        width: 100%;
    }
    
    /* Cart Items */
    .cart-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.9rem;
    }
    
    .cart-item-details {
        flex: 1;
    }
    
    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cart-summary {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .summary-total {
        font-weight: bold;
        font-size: 1.1em;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        margin-top: 10px;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .prescription-container {
            flex-direction: column;
        }
        
        .prescription-sidebar {
            min-width: 100%;
        }
    }
    
    /* Add this to your existing styles */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
</style>

<!-- Add Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="prescription-container">
    <!-- Main Content -->
    <div class="prescription-main">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-prescription"></i> Prescription Details
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="patientSelect">Patient Name</label>
                            <select class="form-control" id="patientSelect" required>
                                <option></option> <!-- Empty option for Select2 placeholder -->
                                <option value="1">Juan Dela Cruz</option>
                                <option value="2">Maria Santos</option>
                                <option value="3">Pedro Reyes</option>
                                <option value="4">Ana Martinez</option>
                                <option value="5">Jose Gonzales</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prescriptionDate">Date</label>
                            <input type="date" class="form-control" id="prescriptionDate" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="medicationSelect">Select Medication</label>
                    <select class="form-control" id="medicationSelect" style="width: 100%;">
                        <option></option> <!-- Empty option for placeholder -->
                        <option value="1">Paracetamol 500mg</option>
                        <option value="2">Amoxicillin 500mg</option>
                        <option value="3">Losartan 50mg</option>
                        <option value="4">Metformin 500mg</option>
                        <option value="5">Omeprazole 20mg</option>
                        <option value="6">Amlodipine 10mg</option>
                        <option value="7">Cetirizine 10mg</option>
                        <option value="8">Salbutamol 2mg/5ml</option>
                        <option value="9">Mefenamic Acid 500mg</option>
                        <option value="10">Vitamin C 500mg</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" class="form-control" id="quantity" value="1">
                </div>
                
                <button class="btn btn-primary" id="addToCartBtn">
                    <i class="fas fa-plus"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="prescription-sidebar">
        <!-- Cart -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-shopping-cart"></i> Cart</span>
                <span class="badge badge-primary" id="cartCount">0</span>
            </div>
            <div class="card-body">
                <div id="cartItems">
                    <p class="text-muted mb-0">Your cart is empty</p>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">₱0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (12%):</span>
                        <span id="tax">₱0.00</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span id="total">₱0.00</span>
                    </div>
                </div>
                
                <!-- Payment Method Selection -->
                <div class="form-group mt-3">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-control" required>
                        <option value="">Select payment method</option>
                        <option value="cash">Cash</option>
                        <option value="insurance">Insurance</option>
                    </select>
                </div>
                
                <!-- Payment Details (will be shown based on selection) -->
                <div id="payment_details" class="mt-2"></div>
                
                <button class="btn btn-primary btn-block mt-3" id="checkoutBtn">
                    <i class="fas fa-check-circle"></i> Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for patient dropdown
    $('#patientSelect').select2({
        placeholder: 'Search and select a patient',
        allowClear: true,
    });
    
    // Initialize Select2 for medication dropdown
    $('#medicationSelect').select2({
        placeholder: 'Select a medication',
        allowClear: true,
    });
    
    let cart = [];
    
    // Handle add to cart button click
    $('#addToCartBtn').on('click', function() {
        const medicationId = $('#medicationSelect').val();
        const medicationName = $('#medicationSelect option:selected').text();
        const quantity = $('#quantity').val();
        
        if (!medicationId || !quantity) {
            alert('Please select a medication and quantity');
            return;
        }
        
        const item = {
            id: Date.now(),
            medicationId: medicationId,
            name: medicationName,
            quantity: quantity,
            price: (Math.random() * 100).toFixed(2) // Random price for demo
        };
        
        cart.push(item);
        updateCart();
        
        // Reset form
        $('#medicationSelect').val(null).trigger('change');
        $('#quantity').val('1');
    });
    
    // Update cart display
    function updateCart() {
        const $cartItems = $('#cartItems');
        $cartItems.empty();
        
        if (cart.length === 0) {
            $cartItems.html('<p class="text-muted mb-0">Your cart is empty</p>');
            $('#cartCount').text('0');
            return;
        }
        
        cart.forEach((item, index) => {
            const $item = $(`
                <div class="cart-item">
                    <div class="cart-item-details">
                        <div class="font-weight-bold">${item.name}</div>
                        <div class="text-muted small">
                            ${item.quantity} pcs • 
                            ₱${(item.price * item.quantity).toFixed(2)}
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-decrease" data-index="${index}">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-increase" data-index="${index}">+</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `);
            
            $cartItems.append($item);
        });
        
        // Update cart count
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        $('#cartCount').text(totalItems);
        
        // Update total
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        $('#subtotal').text(`₱${subtotal.toFixed(2)}`);
        
        // Calculate tax (12% of subtotal)
        const tax = subtotal * 0.12;
        $('#tax').text(`₱${tax.toFixed(2)}`);
        
        // Calculate total
        const total = subtotal + tax;
        $('#total').text(`₱${total.toFixed(2)}`);
    }
    
    // Handle cart item actions
    $(document).on('click', '.btn-increase', function() {
        const index = $(this).data('index');
        cart[index].quantity++;
        updateCart();
    });
    
    $(document).on('click', '.btn-decrease', function() {
        const index = $(this).data('index');
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
            updateCart();
        }
    });
    
    $(document).on('click', '.btn-remove', function() {
        const index = $(this).data('index');
        cart.splice(index, 1);
        updateCart();
    });
    
    // Handle payment method change
    $('#payment_method').on('change', function() {
        const paymentMethod = $(this).val();
        const $paymentDetails = $('#payment_details');
        
        // Clear previous payment details
        $paymentDetails.empty();
        
        if (paymentMethod === 'cash') {
            $paymentDetails.html(`
                <div class="alert alert-info p-2">
                    <i class="fas fa-money-bill-wave"></i> Please prepare cash payment
                </div>
            `);
        } else if (paymentMethod === 'insurance') {
            $paymentDetails.html(`
                <div class="form-group">
                    <label for="insurance_provider">Insurance Provider</label>
                    <select id="insurance_provider" name="insurance_provider" class="form-control form-control-sm" required>
                        <option value="">Select insurance provider</option>
                        <option value="philhealth">PhilHealth</option>
                        <option value="hmi">HMI</option>
                        <option value="maxicare">Maxicare</option>
                        <option value="intellicare">Intellicare</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="insurance_id">Insurance ID</label>
                    <input type="text" id="insurance_id" name="insurance_id" class="form-control form-control-sm" required>
                </div>
            `);
        }
    });
    
    // Handle checkout button
    $('#checkoutBtn').on('click', function() {
        if (cart.length === 0) {
            alert('Your cart is empty');
            return;
        }
        
        const paymentMethod = $('#payment_method').val();
        if (!paymentMethod) {
            alert('Please select a payment method');
            return;
        }
        
        if (paymentMethod === 'insurance') {
            const provider = $('#insurance_provider').val();
            const insuranceId = $('#insurance_id').val();
            
            if (!provider) {
                alert('Please select an insurance provider');
                return;
            }
            
            if (!insuranceId) {
                alert('Please enter your insurance ID');
                return;
            }
        }
        
        // Prepare data for submission
        const orderData = {
            patientId: $('#patientSelect').val(),
            patientName: $('#patientSelect option:selected').text(),
            items: cart,
            payment: {
                method: paymentMethod,
                provider: paymentMethod === 'insurance' ? $('#insurance_provider').val() : null,
                insuranceId: paymentMethod === 'insurance' ? $('#insurance_id').val() : null,
                subtotal: parseFloat($('#subtotal').text().replace('₱', '')),
                tax: parseFloat($('#tax').text().replace('₱', '')),
                total: parseFloat($('#total').text().replace('₱', ''))
            },
            date: $('#prescriptionDate').val()
        };
        
        console.log('Order data:', orderData);
        
        // Here you would typically submit the form or make an AJAX call
        alert('Order submitted successfully!');
        
        // Reset form after submission
        cart = [];
        updateCart();
        $('#payment_method').val('').trigger('change');
        $('#patientSelect').val(null).trigger('change');
    });
});
</script>

<?= $this->endSection() ?>
