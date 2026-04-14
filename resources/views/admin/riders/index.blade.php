@extends('admin.layout')

@section('content')

<div class="container">
    <h2>Riders</h2>
    <a href="{{ route('riders.create') }}" class="btn btn-primary">Add Rider</a>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Availability</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($riders as $rider)
            <tr>
                <td>{{ $rider->id }}</td>
                <td>{{ $rider->name }}</td>
                <td>{{ $rider->phone }}</td>
                <td>{{ $rider->address }}</td>
                <td>{{ $rider->is_available ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('riders.edit', $rider->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('riders.destroy', $rider->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
