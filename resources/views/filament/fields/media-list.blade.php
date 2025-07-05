<div>
    @php
        $mediaIds = $getState() ?? [];
        $mediaItems = \App\Models\Media::whereIn('id', $mediaIds)->get();
    @endphp

    @if($mediaItems->isEmpty())
        <div class="text-gray-500 py-2">No media files uploaded yet.</div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-2">
            @foreach($mediaItems as $media)
                <div class="relative group border border-gray-200 rounded-lg overflow-hidden">
                    @if(Str::startsWith($media->mime_type, 'image/'))
                        <img src="{{ $media->url }}" alt="{{ $media->file_name }}" class="w-full h-32 object-cover">
                    @elseif(Str::startsWith($media->mime_type, 'video/'))
                        <div class="w-full h-32 bg-gray-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    @elseif(Str::startsWith($media->mime_type, 'application/pdf'))
                        <div class="w-full h-32 bg-gray-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    @else
                        <div class="w-full h-32 bg-gray-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                    
                    <div class="p-2 bg-white">
                        <div class="text-xs font-medium truncate" title="{{ $media->file_name }}">
                            {{ $media->file_name }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ \Illuminate\Support\Str::humanFilesize($media->file_size) }}
                        </div>
                    </div>
                    
                    <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                            type="button"
                            wire:click="deleteMedia({{ $media->id }})"
                            class="p-1 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            title="Delete"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
