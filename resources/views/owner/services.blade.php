<x-layouts.dashboard>
<x-admin.shell>
@php($namespace = 'owner')
@include('admin.services-content', ['namespace' => $namespace])
</x-admin.shell>
</x-layouts.dashboard>
