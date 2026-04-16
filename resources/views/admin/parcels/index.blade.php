@extends('layouts.admin')

@section('title', 'Manage Parcels')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">All Parcels</h5>
            <a href="{{ route('admin.parcels.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                Create New Parcel
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover" id="parcelsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tracking #</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Rider</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parcels as $parcel)
                    <tr>
                        <td>{{ $parcel->id }}</td>
                        <td>
                            <span class="fw-bold">{{ $parcel->tracking_number }}</span>
                        </td>
                        <td>{{ $parcel->sender_name }}</td>
                        <td>{{ $parcel->receiver_name }}</td>
                        <td>{{ $parcel->weight }} kg</td>


                        <td>
                            <span class="badge rounded-pill" style="background-color: {{ $parcel->status->color_code }}; color: white;">
                                {{ $parcel->status->display_name }}
                            </span>
                        </td>
                        <td>
                            @if($parcel->assignedRider)
                                {{ $parcel->assignedRider->user->name }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td>{{ $parcel->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.parcels.show', $parcel->id) }}" class="btn btn-sm btn-info">
                                <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                            </a>
                            <a href="{{ route('admin.parcels.edit', $parcel->id) }}" class="btn btn-sm btn-warning">
                                <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                            </a>
                            <form action="{{ route('admin.parcels.destroy', $parcel->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No parcels found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $parcels->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Optional: Add DataTable functionality
    // $('#parcelsTable').DataTable();
</script>
@endpush
