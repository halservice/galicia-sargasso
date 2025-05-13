@props([
    'result',
    'isCode' => true,
])

@if(isset($result))
    {{--        Once the code generation is complete show the result to the user --}}
    @if($isCode)
        <div
            class="rounded-[10px] p-[15px] gap-[5px] w-fit break-words mr-auto mb-5 bg-[#3864fc] text-white mt-5 max-w-4xl">
            <code>
                <pre class="whitespace-pre-wrap">{{ $result }}</pre>
            </code>
        </div>
    @else
        <div
            class="rounded-[10px] p-[15px] gap-[5px] w-fit break-words mr-auto mb-5 bg-orange-400 text-white mt-5 max-w-4xl">
            <p class="whitespace-pre-wrap">{!! Str::markdown($result) !!}</p>
        </div>
    @endif
@endif
