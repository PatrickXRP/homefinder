<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>HomeFinder Kids</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(135deg, #1a3d2b 0%, #2d5a3d 50%, #1a3d2b 100%); min-height: 100dvh; }
        .kid-btn { transition: all 0.2s; }
        .kid-btn:active { transform: scale(0.95); }
        .pin-input { font-size: 2rem; letter-spacing: 1rem; text-align: center; }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <h1 class="text-4xl font-bold text-white text-center mb-2">🏡 HomeFinder</h1>
        <p class="text-white/70 text-center mb-8 text-lg">Wie ben jij?</p>

        @if(session('error'))
            <div class="bg-red-500/20 text-red-200 rounded-2xl p-4 mb-6 text-center text-lg font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div id="kid-select" class="grid grid-cols-2 gap-4">
            @foreach($kids as $kid)
                <button onclick="selectKid('{{ $kid['name'] }}', '{{ $kid['emoji'] }}', '{{ $kid['color'] }}')"
                    class="kid-btn flex flex-col items-center p-6 rounded-3xl bg-white/10 backdrop-blur border-2 border-white/20 hover:bg-white/20 hover:border-white/40 cursor-pointer">
                    <span class="text-6xl mb-2">{{ $kid['emoji'] }}</span>
                    <span class="text-white font-bold text-xl">{{ $kid['name'] }}</span>
                    @if($kid['age'])<span class="text-white/60 text-sm">{{ $kid['age'] }} jaar</span>@endif
                </button>
            @endforeach
        </div>

        <div id="pin-screen" class="hidden">
            <div class="text-center mb-8">
                <span id="selected-emoji" class="text-7xl"></span>
                <h2 id="selected-name" class="text-white font-bold text-2xl mt-2"></h2>
            </div>

            <form method="POST" action="/kids/login" id="pin-form">
                @csrf
                <input type="hidden" name="name" id="pin-name">
                <div class="bg-white/10 backdrop-blur rounded-3xl p-6">
                    <p class="text-white/70 text-center mb-4 text-lg">Voer je PIN in</p>
                    <div class="flex justify-center gap-3 mb-6">
                        <input type="text" maxlength="1" class="w-16 h-20 rounded-2xl bg-white/20 text-white text-4xl text-center font-bold border-2 border-white/30 focus:border-white focus:outline-none pin-digit" inputmode="numeric" pattern="[0-9]">
                        <input type="text" maxlength="1" class="w-16 h-20 rounded-2xl bg-white/20 text-white text-4xl text-center font-bold border-2 border-white/30 focus:border-white focus:outline-none pin-digit" inputmode="numeric" pattern="[0-9]">
                        <input type="text" maxlength="1" class="w-16 h-20 rounded-2xl bg-white/20 text-white text-4xl text-center font-bold border-2 border-white/30 focus:border-white focus:outline-none pin-digit" inputmode="numeric" pattern="[0-9]">
                        <input type="text" maxlength="1" class="w-16 h-20 rounded-2xl bg-white/20 text-white text-4xl text-center font-bold border-2 border-white/30 focus:border-white focus:outline-none pin-digit" inputmode="numeric" pattern="[0-9]">
                    </div>
                    <input type="hidden" name="pin" id="pin-value">
                </div>
            </form>

            <button onclick="goBack()" class="w-full mt-4 py-3 text-white/60 text-center text-lg hover:text-white transition">
                ← Terug
            </button>
        </div>
    </div>

    <script>
        function selectKid(name, emoji, color) {
            document.getElementById('kid-select').classList.add('hidden');
            document.getElementById('pin-screen').classList.remove('hidden');
            document.getElementById('selected-emoji').textContent = emoji;
            document.getElementById('selected-name').textContent = name;
            document.getElementById('pin-name').value = name;
            document.querySelector('.pin-digit').focus();
        }

        function goBack() {
            document.getElementById('kid-select').classList.remove('hidden');
            document.getElementById('pin-screen').classList.add('hidden');
            document.querySelectorAll('.pin-digit').forEach(d => d.value = '');
        }

        // PIN input auto-advance
        document.querySelectorAll('.pin-digit').forEach((input, idx, inputs) => {
            input.addEventListener('input', (e) => {
                if (e.target.value && idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                }
                if (idx === inputs.length - 1 && e.target.value) {
                    const pin = Array.from(inputs).map(i => i.value).join('');
                    document.getElementById('pin-value').value = pin;
                    document.getElementById('pin-form').submit();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                    inputs[idx - 1].focus();
                }
            });
        });
    </script>
</body>
</html>
