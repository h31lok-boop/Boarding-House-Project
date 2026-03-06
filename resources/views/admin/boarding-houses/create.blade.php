<x-layouts.caretaker>
<x-admin.shell>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

  <div class="ui-card p-4 mb-6">
    <h2 class="font-semibold text-xl leading-tight">Add Boarding House</h2>
  </div>

  

  <div class="space-y-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="ui-card p-6">
        @if ($errors->any())
          <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 text-rose-700">
            <ul class="list-disc pl-5 text-sm">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.boarding-houses.store') }}" class="space-y-5">
          @csrf
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" value="{{ old('name') }}" class="w-full border rounded-lg px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Address</label>
            <input id="addressField" name="address" value="{{ old('address') }}" class="w-full border rounded-lg px-3 py-2" required>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Latitude</label>
              <input id="latitudeField" type="number" step="0.0000001" name="latitude" value="{{ old('latitude') }}" class="w-full border rounded-lg px-3 py-2" placeholder="e.g. 6.7440000">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Longitude</label>
              <input id="longitudeField" type="number" step="0.0000001" name="longitude" value="{{ old('longitude') }}" class="w-full border rounded-lg px-3 py-2" placeholder="e.g. 125.3550000">
            </div>
          </div>
          <div class="space-y-2">
            <div class="flex items-center justify-between gap-2 flex-wrap">
              <p class="text-xs ui-muted">Click map to geotag exact location, or drag marker.</p>
              <button type="button" id="useCurrentLocationBtn" class="text-xs px-3 py-1.5 rounded-lg border ui-border hover:bg-[color:var(--surface-2)]">
                Use Current Location
              </button>
            </div>
            <div id="adminCreateMap" class="w-full border rounded-lg" style="height: 320px;"></div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Monthly Payment (PHP)</label>
              <input type="number" step="0.01" min="0" name="monthly_payment" value="{{ old('monthly_payment') }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Approval Status</label>
              <select name="approval_status" class="w-full border rounded-lg px-3 py-2">
                <option value="approved" @selected(old('approval_status') === 'approved')>Approved</option>
                <option value="pending" @selected(old('approval_status', 'pending') === 'pending')>Pending</option>
                <option value="rejected" @selected(old('approval_status') === 'rejected')>Rejected</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Contact Name</label>
              <input name="contact_name" value="{{ old('contact_name') }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Contact Phone</label>
              <input name="contact_phone" value="{{ old('contact_phone') }}" class="w-full border rounded-lg px-3 py-2">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Capacity</label>
            <input type="number" name="capacity" min="1" value="{{ old('capacity', 1) }}" class="w-full border rounded-lg px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('description') }}</textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">House Rules</label>
            <textarea name="house_rules" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('house_rules') }}</textarea>
          </div>
          <label class="inline-flex items-center gap-2 text-sm ">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
            Active
          </label>
          <div class="pt-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save</button>
            <a href="{{ route('admin.boarding-houses.index') }}" class="ml-3 ui-muted ">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const latField = document.getElementById('latitudeField');
      const lngField = document.getElementById('longitudeField');
      const defaultLat = parseFloat(latField.value || '6.7440000');
      const defaultLng = parseFloat(lngField.value || '125.3550000');

      const map = L.map('adminCreateMap').setView([defaultLat, defaultLng], 14);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      let marker = null;
      if (!Number.isNaN(parseFloat(latField.value)) && !Number.isNaN(parseFloat(lngField.value))) {
        marker = L.marker([parseFloat(latField.value), parseFloat(lngField.value)], { draggable: true }).addTo(map);
      }

      function setCoords(lat, lng) {
        latField.value = Number(lat).toFixed(8);
        lngField.value = Number(lng).toFixed(8);
        reverseGeocode(lat, lng);
        if (!marker) {
          marker = L.marker([lat, lng], { draggable: true }).addTo(map);
          marker.on('dragend', function (event) {
            const point = event.target.getLatLng();
            setCoords(point.lat, point.lng);
          });
        } else {
          marker.setLatLng([lat, lng]);
        }
      }

      function reverseGeocode(lat, lng) {
        const addressField = document.getElementById('addressField');
        if (!addressField) {
          return;
        }

        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
          .then((response) => response.ok ? response.json() : Promise.reject())
          .then((payload) => {
            if (!payload || !payload.display_name) {
              return;
            }
            if ((addressField.value || '').trim() === '' || addressField.dataset.autofill === '1') {
              addressField.value = payload.display_name;
              addressField.dataset.autofill = '1';
            }
          })
          .catch(() => {
            // Keep manual address when geocoder fails.
          });
      }

      map.on('click', (event) => {
        setCoords(event.latlng.lat, event.latlng.lng);
      });

      document.getElementById('useCurrentLocationBtn')?.addEventListener('click', () => {
        if (!navigator.geolocation) {
          alert('Geolocation is not supported by this browser.');
          return;
        }

        navigator.geolocation.getCurrentPosition((position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          setCoords(lat, lng);
          map.setView([lat, lng], 16);
        }, () => {
          alert('Unable to retrieve your location. Please allow location permission.');
        }, {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0
        });
      });
    });
  </script>
</x-admin.shell>
</x-layouts.caretaker>
