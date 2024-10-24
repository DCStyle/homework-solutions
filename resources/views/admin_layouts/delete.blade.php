@extends('admin_layouts.admin')

@section('content')
    <!-- Modal content -->
    <form action="{{ $confirmLink }}" method="post" class="mx-auto max-w-xl p-4 text-center bg-white rounded-lg shadow sm:p-5">
        @csrf
        @method('DELETE')

        <svg class="text-gray-400 w-11 h-11 mb-3.5 mx-auto" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>

        <p class="mb-4 text-gray-500">Are you sure you want to delete this?</p>
        <p class="mb-4 text-dark text-2xl font-bold">
            {{ $name }}
        </p>

        <div class="flex justify-center items-center space-x-4">
            <a href="{{ $backLink  }}" class="inline-block py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10">
                {{ __('Cancel') }}
            </a>

            <button type="submit" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300">
                {{ __('Delete') }}
            </button>
        </div>
    </form>
@endsection
