@extends('admin.layout')

@section('content')

<h2 class="mb-4">Add Order</h2>

<div class="card">
    <div class="card-body">

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf

            <div class="row">

                <!-- Customer Info -->
                <div class="col-md-6 mb-3">
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Customer Phone</label>
                    <input type="text" name="customer_phone" class="form-control" required>
                </div>

                <!-- Address -->
                <div class="col-md-12 mb-3">
                    <label>Drop Address</label>
                    <textarea name="drop_address" class="form-control" required></textarea>
                </div>

                <!-- Location -->
                <div class="col-md-6 mb-3">
                    <label>Latitude</label>
                    <input type="text" name="drop_latitude" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Longitude</label>
                    <input type="text" name="drop_longitude" class="form-control">
                </div>

                <!-- Amount -->
                <div class="col-md-6 mb-3">
                    <label>Total Amount</label>
                    <input type="number" name="total_amount" class="form-control" required>
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="assigned">Assigned</option>
                        <option value="picked_up">Picked Up</option>
                        <option value="delivered">Delivered</option>
                        <option value="failed_delivered">Failed Delivered</option>
                        <option value="returned">Returned</option>
                    </select>
                </div>

                <!-- Rider -->
                <div class="col-md-6 mb-3">
                    <label>Rider ID</label>
                    <input type="number" name="rider_id" class="form-control">
                </div>

                <!-- Return Type -->
                <div class="col-md-6 mb-3">
                    <label>Return Type</label>
                    <select name="return_type" id="return_type" class="form-control">
                        <option value="">Select</option>
                        <option value="hub">Hub</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>

                <!-- Hub ID (HIDDEN BY DEFAULT) -->
                <div class="col-md-6 mb-3" id="hub_field" style="display:none;">
                    <label>Hub ID</label>
                    <input type="number" name="hub_id" class="form-control">
                </div>

                <!-- Return Address (HIDDEN BY DEFAULT) -->
                <div class="col-md-6 mb-3" id="address_field" style="display:none;">
                    <label>Return Address</label>
                    <textarea name="return_address" class="form-control"></textarea>
                </div>

            </div>

            <button class="btn btn-success">Save Order</button>
        </form>

    </div>
</div>

<script>
    document.getElementById('return_type').addEventListener('change', function () {
        let value = this.value;

        let hubField = document.getElementById('hub_field');
        let addressField = document.getElementById('address_field');

        if (value === 'hub') {
            hubField.style.display = 'block';
            addressField.style.display = 'none';
        } else if (value === 'owner') {
            hubField.style.display = 'none';
            addressField.style.display = 'block';
        }
    });
</script>

@endsection
