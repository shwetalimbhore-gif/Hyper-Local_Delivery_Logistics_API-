@extends('admin.layout')

@section('content')
<div class="container">
    <h2>Hubs</h2>
    <a href="{{ route('hubs.create') }}" class="btn btn-primary">Add Hub</a>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hubs as $hub)
            <tr>
                <td>{{ $hub->id }}</td>
                <td>{{ $hub->name }}</td>
                <td>{{ $hub->address }}</td>
                <td>{{ $hub->latitude }}</td>
                <td>{{ $hub->longitude }}</td>
                <td>
                    <a href="{{ route('hubs.edit', $hub->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('hubs.destroy', $hub->id) }}" method="POST" style="display:inline;">
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
