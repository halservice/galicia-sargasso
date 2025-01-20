<a href="{{ $href }}" class="block w-[94%]">
    <button
        class="group flex items-left w-[305px] h-[100px] p-[15px] border-0 rounded-[8px] transition-all duration-300 ease-in-out
        {{ $active ? 'bg-gray-100 text-black' : 'bg-white text-[#74c7ec]' }}
        hover:bg-[#f3f3f9]">
        <div class="flex items-center gap-[15px] w-full">
            <span class="text-2xl min-w-[40px] {{ $active ? 'text-[#89b4fa]' : 'text-[#74c7ec] group-hover:text-[#89b4fa]' }}">
                {!! $symbol !!}
            </span>
            <div class="flex flex-col items-start gap-[4px]">
                <span class="font-bold text-[17px] text-left font-poppins
                    {{ $active ? 'text-[#89b4fa]' : 'text-[#74c7ec] group-hover:text-[#89b4fa]' }}">
                    {{ $slot }}
                </span>
                <span class="text-[13px] text-left
                    {{ $active ? 'text-gray-600' : 'text-[#7f849c]' }}">
                    {{ $description }}
                </span>
            </div>
        </div>
    </button>
</a>
