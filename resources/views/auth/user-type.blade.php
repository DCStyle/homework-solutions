@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-indigo-600">Welcome to EduConnect</h2>
                <p class="mt-2 text-gray-600">Please tell us who you are</p>
            </div>

            <form class="mt-8 space-y-6" method="POST" action="{{ route('user-type.update') }}">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-4">I am a:</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="relative flex p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500">
                                <input type="radio" name="user_type" value="student" class="sr-only peer" checked>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-indigo-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                    </svg>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Student</span>
                                        <p class="text-xs text-gray-500">I'm here to learn and access educational content</p>
                                    </div>
                                </div>
                                <div class="absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-indigo-500 pointer-events-none" aria-hidden="true"></div>
                            </label>
                        </div>
                        <div>
                            <label class="relative flex p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500">
                                <input type="radio" name="user_type" value="teacher" class="sr-only peer">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-indigo-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                    </svg>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Teacher</span>
                                        <p class="text-xs text-gray-500">I'm here to create content and teach students</p>
                                    </div>
                                </div>
                                <div class="absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-indigo-500 pointer-events-none" aria-hidden="true"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
