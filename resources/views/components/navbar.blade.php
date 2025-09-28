<nav class="fixed top-0 z-50 w-full bg-gradient-to-r from-primary-600 to-primary-500 shadow-2xl">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <!-- Left Section -->
            <div class="flex items-center justify-start">
                <!-- Sidebar Toggle -->
                <button data-drawer-target="sidebar" data-drawer-toggle="sidebar" aria-controls="sidebar" type="button"
                    class="inline-flex items-center p-2 text-sm text-gray-200 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                        <path clip-rule="evenodd" fill-rule="evenodd"
                            d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 5.25A.75.75 0 012.75 9h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10zm.75 4.25a.75.75 0 000 1.5h14.5a.75.75 0 000-1.5H2.75z">
                        </path>
                    </svg>
                </button>
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex ml-2 md:mr-24">
                    <span class="self-center text-xl text-white font-semibold sm:text-2xl whitespace-nowrap">
                        Berkah Jaya
                    </span>
                </a>
            </div>

            <!-- Right Section -->
            <div class="flex items-center">
                <div class="flex items-center ml-3 relative">
                    <!-- Avatar -->
                    <button type="button"
                        class="flex mx-3 text-sm bg-gray-800 rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300"
                        id="user-menu-button" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                        <span class="sr-only">Open user menu</span>
                        <img class="w-8 h-8 rounded-full"
                            src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="user photo">
                    </button>

                    <!-- Dropdown -->
                    <div class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow"
                        id="dropdown-user">
                        <div class="py-3 px-4">
                            <span class="block text-sm font-semibold text-gray-900">Neil Sims</span>
                            <span class="block text-sm text-gray-500 truncate">name@flowbite.com</span>
                        </div>
                        <ul class="py-1 text-gray-700" aria-labelledby="dropdown-user">
                            {{-- <li>
                                <a href="#" class="block py-2 px-4 text-sm hover:bg-gray-100">My Profile</a>
                            </li> --}}
                            <li>
                                <a href="{{ route('setting.index') }}"
                                    class="block py-2 px-4 text-sm hover:bg-gray-100">
                                    Pengaturan
                                </a>
                            </li>
                            {{-- <li>
                                <a href="#" class="block py-2 px-4 text-sm hover:bg-gray-100">Sign Out</a>
                            </li> --}}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>