@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-screen flex flex-col">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Virtual Presentation Room</h1>
            <p class="text-sm text-slate-500">
                {{ $eventName }} - {{ $studentName }}
            </p>
        </div>
        <a href="{{ url()->previous() }}" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-sm font-medium transition-colors">
            Leave Room
        </a>
    </div>

    <!-- Jitsi Container -->
    <div id="meet" class="flex-1 w-full bg-slate-900 rounded-xl overflow-hidden shadow-lg border border-slate-200 min-h-[600px]"></div>
</div>

@push('scripts')
<script src="https://meet.jit.si/external_api.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const domain = 'meet.jit.si';
        const options = {
            roomName: '{{ $roomName }}',
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#meet'),
            userInfo: {
                email: '{{ $user->email }}',
                displayName: '{{ addslashes($user->name) }}'
            },
            configOverwrite: {
                startWithAudioMuted: true,
                startWithVideoMuted: false,
                prejoinPageEnabled: true
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                    'security'
                ],
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    });
</script>
@endpush
@endsection
