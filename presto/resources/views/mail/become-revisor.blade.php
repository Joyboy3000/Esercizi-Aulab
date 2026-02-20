<h1>Richiesta per diventare revisore</h1>
<p>L'utente <strong>{{ $user->name }}</strong> ({{ $user->email }}) ha richiesto di diventare revisore.</p>
<p>
    <a href="{{ route('make.revisor', ['user' => $user]) }}">Rendi revisore {{ $user->name }}</a>
</p>
