# Matti service boundary

`matti.geojson` is the application's shared service-area boundary for **Matti,
Digos City, Davao del Sur, Davao Region**. It contains one GeoJSON Feature with a
Polygon in WGS 84 / EPSG:4326. Coordinates use `[longitude, latitude]` order.
The single closed ring contains 315 positions. No coordinates were simplified,
traced, or replaced with a bounding rectangle.

## Provenance

- Provider: Philippine Statistics Authority (PSA), hosted by GeoRisk Philippines.
- Layer: [Barangay Boundary, PSA/BarangayPopMF, layer 0](https://ulap-nga.georisk.gov.ph/arcgis/rest/services/PSA/BarangayPopMF/MapServer/0).
- [Parent service](https://ulap-nga.georisk.gov.ph/arcgis/rest/services/PSA/BarangayPopMF/MapServer) states `Copyright Text: PSA`.
- Retrieved: 2026-09-10.
- PSGC: `1102403014`; correspondence code: `112403014`.
- The layer's [item information](https://ulap-nga.georisk.gov.ph/arcgis/rest/services/PSA/BarangayPopMF/MapServer/0/iteminfo) does not specify a named license (`licenseInfo` is empty). No additional license is asserted here.

Exact download query:

```text
https://ulap-nga.georisk.gov.ph/arcgis/rest/services/PSA/BarangayPopMF/MapServer/0/query?where=brgy_code%20%3D%20%27112403014%27&outFields=brgy_name,city_name,prov_name,reg_name,brgy_code,psgc_10d&outSR=4326&f=geojson
```

The source response is a FeatureCollection containing this single Feature. The
committed file unwraps that Feature and adds source attribution, retrieval date,
service name, and an interior map center. Original source properties and geometry
are preserved. The downloaded response's SHA-256 is
`E21EDEF0749BA7382C524277717703162D947EDD7DFA361CB274A43F6E7AFC67`.

The public source polygon was rendered and compared visually with the supplied
map screenshot. Its northern hook, western bends, winding eastern border, and
southern extension closely match the supplied outline. The data comes from the
PSA service; it is not geometry extracted from the screenshot or from Google Maps.

## Coordinates for verification

The map center `[125.3062, 6.76778]` was checked to lie inside the polygon; it is
an interior display point, not a computed centroid. Matti Barangay Hall at
`[125.30782, 6.76831]` and the southern extension at `[125.308, 6.746]` are also
inside. `[125.30, 6.75]` and `[125.317, 6.78]` lie inside the bounding rectangle
but outside the polygon and are useful regression cases for geofence validation.

Bounds: west `125.29189347`, east `125.318616922`, south `6.74037865800005`,
north `6.79136916800007`. Bounds constrain map navigation; the polygon determines
service eligibility.
