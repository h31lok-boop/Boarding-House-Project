<x-layouts.dashboard>
<x-admin.shell>
@php($namespace = 'admin')
@include('admin.services-content', ['namespace' => $namespace])
</x-admin.shell>
</x-layouts.dashboard>
