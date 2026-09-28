<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $form->name }} — {{ $form->company?->name ?? 'Request' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0f12; --surface: #13161b; --surface2: #1a1e25;
            --border: rgba(255,255,255,0.08); --border2: rgba(255,255,255,0.13);
            --text: #e8eaf0; --muted: #6b7385; --accent: #4ade80; --accent2: #a78bfa;
            --font: 'DM Sans', sans-serif; --mono: 'DM Mono', monospace;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: var(--font); font-size: 14px; background: var(--bg); color: var(--text); min-height: 100vh; padding: 32px 16px 48px; }
        .wrap { max-width: 480px; margin: 0 auto; }
        .brand { font-size: 11px; font-family: var(--mono); color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 24px 22px; }
        h1 { font-size: 20px; font-weight: 600; margin: 0 0 6px; letter-spacing: -0.3px; }
        .sub { font-size: 13px; color: var(--muted); margin-bottom: 22px; line-height: 1.45; }
        label { display: block; font-size: 10px; font-family: var(--mono); text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); margin-bottom: 6px; }
        input, textarea, select {
            width: 100%; padding: 10px 12px; margin-bottom: 16px;
            background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px;
            color: var(--text); font-family: var(--font); font-size: 14px;
        }
        input:focus, textarea:focus { outline: none; border-color: var(--accent2); }
        textarea { resize: vertical; min-height: 96px; }
        button {
            width: 100%; padding: 12px 16px; border-radius: 10px; cursor: pointer; font-family: var(--font);
            font-size: 14px; font-weight: 600; background: rgba(74,222,128,0.15); color: var(--accent);
            border: 1px solid rgba(74,222,128,0.35); transition: background 0.15s;
        }
        button:hover { background: rgba(74,222,128,0.22); }
        .ok { padding: 12px 14px; margin-bottom: 16px; border-radius: 8px; font-size: 13px;
            color: var(--accent); border: 1px solid rgba(74,222,128,0.35); background: rgba(74,222,128,0.08); }
        .pill { display: inline-block; font-size: 11px; font-family: var(--mono); color: var(--accent2);
            padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(167,139,250,0.35); margin-bottom: 16px; }
    </style>
</head>
<body>
<div class="wrap">
    @if($form->company?->name)
    <div class="brand">{{ $form->company->name }}</div>
    @endif
    <div class="card">
        @if(session('success'))
        <div class="ok">{{ session('success') }}</div>
        @endif
        <h1>{{ $form->name }}</h1>
        <p class="sub">
            Submit a request. It will be added as a task
            @if($form->project)
            on <strong style="color:var(--text); font-weight:500;">{{ $form->project->name }}</strong>
            @endif
            .
        </p>
        @if($form->project)
        <div class="pill">Project: {{ $form->project->name }}</div>
        @endif
        <form method="POST">
            @csrf
            <label for="title">Title *</label>
            <input id="title" name="title" required placeholder="Brief summary of your request">
            <label for="description">Details</label>
            <textarea id="description" name="description" placeholder="Add context, links, or requirements"></textarea>
            <label for="due_date">Due date</label>
            <input type="date" id="due_date" name="due_date">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
            </select>
            <button type="submit">Submit request</button>
        </form>
    </div>
</div>
</body>
</html>
