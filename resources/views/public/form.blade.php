<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $form->name }}</title>
    <style>
        body { font-family: sans-serif; background:#0d0f12; color:#e8eaf0; padding:40px 16px; }
        .box { max-width:520px; margin:0 auto; background:#13161b; border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:24px; }
        input, textarea { width:100%; padding:10px; margin:8px 0 14px; background:#1a1e25; border:1px solid rgba(255,255,255,.13); color:#fff; border-radius:8px; }
        button { background:rgba(74,222,128,.15); color:#4ade80; border:1px solid rgba(74,222,128,.3); padding:10px 16px; border-radius:8px; cursor:pointer; }
        .ok { color:#4ade80; margin-bottom:12px; }
    </style>
</head>
<body>
<div class="box">
    <h2>{{ $form->name }}</h2>
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    <form method="POST">
        @csrf
        <label>Title</label>
        <input name="title" required>
        <label>Details</label>
        <textarea name="description" rows="4"></textarea>
        <label>Due date</label>
        <input type="date" name="due_date">
        <label>Priority</label>
        <input name="priority" value="medium">
        <button>Submit request</button>
    </form>
</div>
</body>
</html>
