@extends('admin.layout')

@section('content')
<div class="container">
    <h2>Add Hub</h2>

    <form method="POST" action="{{ route('hubs.store') }}">
        @csrf

        <input type="text" name="name" placeholder="Name" class="form-control mb-2">
        <textarea name="address" placeholder="Address" class="form-control mb-2"></textarea>

       <div id="map" style="height: 400px;"></div>

        <input type="text" name="latitude" id="latitude" class="form-control mt-2" placeholder="Latitude">
        <input type="text" name="longitude" id="longitude" class="form-control mt-2" placeholder="Longitude">

        <button class="btn btn-success">Save</button>
    </form>
</div>

<div id="map" style="height: 400px;"></div>

<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}"></script>

<script>
    let map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 18.5204, lng: 73.8567 }, // Pune default
        zoom: 12
    });

    let marker;

    map.addListener('click', function (e) {
        let lat = e.latLng.lat();
        let lng = e.latLng.lng();

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (marker) {
            marker.setMap(null);
        }

        marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map
        });
    });
</script>

@endsection
