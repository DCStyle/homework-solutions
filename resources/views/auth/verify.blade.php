@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-center">{{ __('Verify Your Email Address') }}</h2>

            <div class="mb-4">
                @if (session('resent'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                        <p>{{ __('A fresh verification link has been sent to your email address.') }}</p>
                    </div>
                @endif
            </div>

            <p class="mb-4 text-gray-600">
                {{ __('Before proceeding, please check your email for a verification link.') }}
            </p>
            <p class="mb-4 text-gray-600">
                {{ __('If you did not receive the email') }},
            </p>

            <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="text-indigo-600 hover:underline">{{ __('click here to request another') }}</button>.
            </form>
        </div>
    </div>
@endsection
