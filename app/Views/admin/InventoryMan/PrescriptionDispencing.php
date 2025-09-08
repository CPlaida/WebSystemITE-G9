<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Dispensing - St. Peter Hospital</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f6f8fb;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .page-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .card {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .card-header h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.2em;
        }

        .card-body {
            padding: 20px;
        }

        .prescription-form {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: transparent;
            color: #6c757d;
            border: 1px solid #6c757d;
        }

        .btn-secondary:hover {
            background-color: #f8f9fa;
            color: #5a6268;
            border-color: #5a6268;
        }

        /* Layout */
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            body {
                padding: 10px;
            }
        }

        /* Cart Styles */
        .cart-container {
            margin-top: 30px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item-details {
            flex-grow: 1;
        }
        
        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .quantity-btn {
            width: 25px;
            height: 25px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .cart-total {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        
        .empty-cart {
            text-align: center;
            color: #6c757d;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Prescription Dispensing</h1>
            <a href="<?= base_url('/dashboard') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="main-content">
            <div class="main-section">
                <div class="card">
                    <div class="card-header">
                        <h3>Add Medication</h3>
                    </div>
                    <div class="card-body">
                        <form id="prescriptionForm" class="prescription-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="patient">Patient Name</label>
                                    <input type="text" id="patient" placeholder="Enter Patient Name" required>
                                </div>
                                <div class="form-group">
                                    <label for="doctor">Prescribing Doctor</label>
                                    <input type="text" id="doctor" placeholder="Enter Doctor's Name" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="medication">Medication</label>
                                    <select id="medication" required>
                                        <option value="">Select Medication</option>
                                        <!-- Medication options will be populated by JavaScript -->
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    <input type="number" id="quantity" min="1" value="1" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea id="notes" rows="2" placeholder="Additional instructions"></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" id="addToCart" class="btn btn-primary">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="cart-section">
                <div class="card">
                    <div class="card-header">
                        <h3>Prescription Cart</h3>
                    </div>
                    <div class="card-body">
                        <div id="cartItems" class="cart-container">
                            <div class="empty-cart">No items added yet</div>
                        </div>
                        <div class="cart-actions">
                            <button type="button" id="checkoutBtn" class="btn btn-primary" style="width: 100%; margin-top: 15px;" disabled>
                                Process Prescription
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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
</body>
</html>
