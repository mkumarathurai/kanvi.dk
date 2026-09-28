<!DOCTYPE html>
<html lang="da"><head><meta charset="utf-8"><title>Din arrangøradgang til Kanvi</title></head>
<body style="font-family:Arial,sans-serif;color:#0B2540;line-height:1.6;max-width:560px;margin:40px auto;padding:24px">
<p style="font-size:28px;font-weight:bold">kanvi</p>
<h1 style="font-size:24px">{{ $pollTitle }}</h1>
<p>{{ $registerEmail ? 'Bekræft din mailadresse, så du kan få arrangøradgangen tilbage, hvis du mister den.' : 'Her kan du få arrangøradgangen tilbage til din afstemning.' }}</p>
<p><a href="{{ $accessUrl }}" style="display:inline-block;padding:14px 22px;background:#138442;color:white;border-radius:8px;text-decoration:none">{{ $registerEmail ? 'Bekræft mail og åbn afstemningen' : 'Åbn som arrangør' }} →</a></p>
<p>Linket kan bruges én gang og udløber kl. {{ $expiresAt }} (dansk tid). På siden får du et administrationslink, som du kan gemme.</p>
<p>Del ikke denne mail eller linket med gruppen. Alle med linket kan få arrangøradgang.</p>
<p>Har du ikke bedt om mailen, kan du ignorere den. Din nuværende adgang ændres ikke.</p>
</body></html>
