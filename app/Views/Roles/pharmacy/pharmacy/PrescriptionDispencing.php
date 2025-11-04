<?php $this->extend('partials/header') ?>

<?= $this->section('title') ?>Prescription Dispensing<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Add Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

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
                            <label for="patientInput">Patient Name</label>
                            <div class="patient-autocomplete">
                                <input type="text" class="form-control" id="patientInput" placeholder="Type patient name" autocomplete="off" required>
                                <div id="patientSuggestions" class="autocomplete-menu" hidden></div>
                                <input type="hidden" id="patientId">
                            </div>
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
                        <option></option>
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
                
                <!-- Payment -->
                <div class="form-group mt-3">
                    <label for="amount_received" style="font-weight:600;">Amount Received</label>
                    <div style="display:flex; align-items:stretch;">
                        <span style="background:#f3f4f6; border:1px solid #e5e7eb; border-right:0; color:#374151; padding:8px 10px; border-radius:6px 0 0 6px; font-weight:600;">₱</span>
                        <input type="number" id="amount_received" class="form-control" min="0" step="0.01" placeholder="Enter cash received" style="border-radius:0 6px 6px 0; text-align:left;">
                    </div>
                    <div style="display:flex; justify-content:flex-end; align-items:center; margin-top:6px;">
                        <span id="change_badge" style="background:#eafaf1; color:#0f7a43; border:1px solid #b7f0cf; padding:4px 10px; border-radius:9999px; font-weight:600; font-size:12px;">Change: ₱0.00</span>
                    </div>
                </div>
                
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
    // Base URL helper
   const API_BASE = '<?= site_url('api/pharmacy') ?>';

    let patientList = [];
    let activeIndex = -1;
    function debounce(fn, wait){ let t; return function(...a){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,a), wait); } }

    function renderPatientSuggestions(list){
        const $menu = $('#patientSuggestions');

        // Ensure wrapper context and menu styled as a card
        $('.patient-autocomplete').css({ position: 'relative' });
        $menu.css({
            position: 'absolute',
            top: 'calc(100% + 4px)',
            left: 0,
            right: 0,
            zIndex: 2000,
            background: '#fff',
            border: '1px solid #e5e7eb',
            borderRadius: '10px',
            boxShadow: '0 10px 24px rgba(17, 24, 39, 0.10)',
            padding: '6px 0',
            maxHeight: '320px',
            overflowY: 'auto',
            display: 'block'
        });

        $menu.empty();
        activeIndex = -1;

        if (!list || list.length === 0){
            $menu.attr('hidden', true);
            return;
        }

        list.forEach((p,i)=>{
            $menu.append(
                `<div class="autocomplete-item" data-index="${i}" data-id="${p.id}" data-name="${p.name}" style="
                    display:flex;align-items:center;justify-content:space-between;
                    padding:12px 14px;font-size:15px;line-height:1.3;color:#111827;cursor:pointer;background:#fff;text-align:left;
                    border-bottom: 1px solid #eef2f7;
                ">
                    <span>${p.name}</span>
                </div>`
            );
        });

        // Add hover styles inline for reliability
        // Add dividers and hover behavior inline
        $menu.find('.autocomplete-item + .autocomplete-item').css('border-top','1px solid #eef2f7');
        $menu.off('mouseenter mouseleave', '.autocomplete-item')
             .on('mouseenter', '.autocomplete-item', function(){ $(this).css('background', '#f5f7fb'); })
             .on('mouseleave', '.autocomplete-item', function(){ $(this).css('background', '#fff'); });

        $menu.removeAttr('hidden');
    }

    const fetchPatients = debounce(function(q){
        if (!q || q.length < 1){ $('#patientSuggestions').attr('hidden', true).empty(); return; }
        fetch(API_BASE + '/patients?term=' + encodeURIComponent(q))
            .then(r=>r.json())
            .then(list=>{ patientList = list || []; renderPatientSuggestions(patientList); })
            .catch(()=>{ $('#patientSuggestions').attr('hidden', true).empty(); });
    },200);

    $('#patientInput').on('input', function(){
        $('#patientId').val('');
        const v = this.value.trim();
        fetchPatients(v);
    });

    $(document).on('mousedown', '#patientSuggestions .autocomplete-item', function(e){
        const name = $(this).data('name');
        const id = Number($(this).data('id')) || 0;
        $('#patientInput').val(name);
        $('#patientId').val(id);
        $('#patientSuggestions').attr('hidden', true).empty();
    });

    $('#patientInput').on('keydown', function(e){
        const $items = $('#patientSuggestions .autocomplete-item');
        if ($items.length === 0) return;
        if (e.key === 'ArrowDown'){ e.preventDefault(); activeIndex = (activeIndex + 1) % $items.length; $items.removeClass('active').eq(activeIndex).addClass('active'); }
        else if (e.key === 'ArrowUp'){ e.preventDefault(); activeIndex = (activeIndex - 1 + $items.length) % $items.length; $items.removeClass('active').eq(activeIndex).addClass('active'); }
        else if (e.key === 'Enter'){ if (activeIndex >= 0){ e.preventDefault(); $items.eq(activeIndex).trigger('mousedown'); } }
        else if (e.key === 'Escape'){ $('#patientSuggestions').attr('hidden', true).empty(); }
    });

    $(document).on('click', function(e){
        if (!$(e.target).closest('.patient-autocomplete').length){ $('#patientSuggestions').attr('hidden', true).empty(); }
    });
    
    // Initialize Select2 for medication dropdown with AJAX
    $('#medicationSelect').select2({
        placeholder: 'Select a medication',
        allowClear: true,
        ajax: {
            url: API_BASE + '/medications',
            dataType: 'json',
            delay: 200,
            data: params => ({ term: params.term || '' }),
            processResults: data => ({
                results: (data || [])
                    .filter(m => Number(m.stock) > 0)
                    .map(m => ({
                        id: m.id,
                        text: `${m.name}${m.brand ? ' (' + m.brand + ')' : ''}`,
                        name: m.name,
                        price: parseFloat(m.price),
                        stock: m.stock
                    }))
            }),
            transport: function (params, success, failure) {
                const request = $.ajax(params);
                request.then(success);
                request.fail((jqXHR) => { console.error('Medications load failed', jqXHR.responseText); failure(jqXHR); });
                return request;
            }
        },
        minimumInputLength: 0,
        width: '100%'
    }).on('select2:open', function() {
        const searchField = document.querySelector('.select2-container--open .select2-search__field');
        if (searchField) {
            searchField.value = '';
            const e = new Event('input', { bubbles: true });
            searchField.dispatchEvent(e);
        }
    }).on('select2:select', function(e) {
        const data = e.params.data || {};
        const stock = Number(data.stock || 0);
        // Reflect stock limit in quantity input
        $('#quantity').attr('min', 1).attr('max', Math.max(stock, 1));
        if (stock <= 0) {
            alert('Selected medicine is out of stock.');
        }
    });
    
    let cart = [];
    
    // Handle add to cart button click
    $('#addToCartBtn').on('click', function() {
        const selected = $('#medicationSelect').select2('data')[0];
        const medicationId = String(selected?.id ?? '');
        const medicationName = selected?.name || selected?.text || '';
        const price = Number(selected?.price || 0);
        const quantity = Number($('#quantity').val());
        const stock = Number(selected?.stock || 0);
        
        if (!medicationId || !quantity) {
            alert('Please select a medication and quantity');
            return;
        }
        if (quantity <= 0) {
            alert('Quantity must be at least 1');
            return;
        }
        // Compute how many of this med already in cart
        const inCartQty = cart.reduce((sum, i) => i.medicationId === medicationId ? sum + Number(i.quantity) : sum, 0);
        const remaining = stock - inCartQty;
        if (stock <= 0 || quantity > remaining) {
            alert(`Insufficient stock. Available: ${Math.max(remaining, 0)}`);
            return;
        }
        
        // Merge with existing item of same med if present
        const existing = cart.find(i => i.medicationId === medicationId);
        if (existing) {
            existing.quantity += quantity;
        } else {
            const item = {
                id: Date.now(),
                medicationId: medicationId,
                name: medicationName,
                quantity: quantity,
                price: price,
                stock: stock
            };
            cart.push(item);
        }
        updateCart();
        
        // Reset form
        $('#medicationSelect').val(null).trigger('change');
        $('#quantity').val('1').attr('max', '');
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
        // Refresh change preview if payment input is present
        if (document.getElementById('amount_received')) { setTimeout(updateChange, 0); }
    }
    
    // Handle cart item actions
    $(document).on('click', '.btn-increase', function() {
        const index = $(this).data('index');
        const item = cart[index];
        const totalOther = cart.reduce((sum, i, idx) => (i.medicationId === item.medicationId && idx !== index) ? sum + Number(i.quantity) : sum, 0);
        const remaining = Number(item.stock || 0) - totalOther;
        if (item.quantity + 1 > remaining) {
            alert('Insufficient stock for this medicine');
            return;
        }
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
    
    // Change calculator for cash payment
    const formatMoney = v => '₱' + (Number(v)||0).toFixed(2);
    function getTotal(){ return parseFloat($('#total').text().replace('₱','')) || 0; }
    function updateChange(){
        const paid = parseFloat($('#amount_received').val() || '0');
        const change = paid - getTotal();
        $('#change_badge').text('Change: ' + formatMoney(Math.max(change,0)));
    }
    $(document).on('input', '#amount_received', updateChange);
    
    // Handle checkout button
    $('#checkoutBtn').on('click', function() {
        if (cart.length === 0) {
            alert('Your cart is empty');
            return;
        }
        
        const paymentMethod = 'cash';
        const amountReceived = parseFloat($('#amount_received').val() || '0');
        const totalDue = parseFloat($('#total').text().replace('₱','')) || 0;
        if (amountReceived < totalDue) {
            alert('Amount received is insufficient to cover the total.');
            return;
        }
        
        
        // Validate patient selection (must match a suggestion so we have an ID)
        const pid = Number($('#patientId').val() || 0);
        const pname = $('#patientInput').val().trim();
        if (!pname || pid <= 0) {
            alert('Please select a valid patient from the suggestions.');
            return;
        }

        // Prepare data for submission
        const orderData = {
            patient_id: pid,
            patient_name: pname,
            items: cart.map(i => ({
                medicine_id: i.medicationId,
                medicine_name: i.name,
                quantity: i.quantity,
                price: Number(i.price)
            })),
            payment_method: paymentMethod,
            subtotal: parseFloat($('#subtotal').text().replace('₱', '')),
            tax: parseFloat($('#tax').text().replace('₱', '')),
            total: parseFloat($('#total').text().replace('₱', '')),
            amount_paid: amountReceived,
            change: Math.max(amountReceived - (parseFloat($('#total').text().replace('₱', '')) || 0), 0),
            date: $('#prescriptionDate').val()
        };

        
        // Submit to backend
        fetch(API_BASE + '/transaction/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        })
        .then(res => res.json())
        .then(resp => {
            if (!resp.success) throw new Error(resp.message || 'Transaction failed');
            alert(`Transaction created! #${resp.transaction_number}`);
        })
        .catch(err => {
            alert('Error: ' + err.message);
            return;
        });
        
        // Reset form after submission
        cart = [];
        updateCart();
        $('#payment_method').val('').trigger('change');
        $('#patientInput').val('');
        $('#patientId').val('');
        $('#patientSuggestions').attr('hidden', true).empty();
    });
});
</script>

<?= $this->endSection() ?>
