<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Записи пациентов — {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</title>
    <style>
        :root {
            --ink: #1a1a1a;
            --muted: #5c5c5c;
            --line: #cfcfcf;
            --soft: #f7f7f7;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--ink);
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
            line-height: 1.45;
            background: #fff;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1.25rem;
            background: #f3f4f6;
            border-bottom: 1px solid var(--line);
        }

        .toolbar .actions { display: flex; gap: 0.5rem; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border: 1px solid #9aa3af;
            border-radius: 6px;
            background: #fff;
            color: #111;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-primary {
            background: #1f4b7a;
            border-color: #1f4b7a;
            color: #fff;
        }

        .sheet {
            max-width: 980px;
            margin: 1.5rem auto 2.5rem;
            padding: 0 1.25rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-end;
            padding-bottom: 0.85rem;
            border-bottom: 2px solid var(--ink);
            margin-bottom: 1.25rem;
        }

        .header h1 {
            margin: 0 0 0.25rem;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .header .meta {
            color: var(--muted);
            font-size: 12px;
        }

        .header .date-block {
            text-align: right;
        }

        .header .date-block strong {
            display: block;
            font-size: 1.1rem;
        }

        .summary {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
            color: var(--muted);
            font-size: 12px;
        }

        .doctor-block {
            margin-bottom: 1.5rem;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .doctor-block h2 {
            margin: 0 0 0.55rem;
            font-size: 1rem;
            padding: 0.4rem 0.55rem;
            background: var(--soft);
            border-left: 3px solid #1f4b7a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid var(--line);
            padding: 0.4rem 0.5rem;
            vertical-align: top;
            text-align: left;
        }

        th {
            background: #efefef;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #333;
        }

        td.time {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            width: 90px;
        }

        .empty {
            padding: 2rem;
            text-align: center;
            color: var(--muted);
            border: 1px dashed var(--line);
        }

        .footer {
            margin-top: 2rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 11px;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            .toolbar { display: none !important; }
            .sheet {
                max-width: none;
                margin: 0;
                padding: 0;
            }
            a { color: inherit; text-decoration: none; }
            body { font-size: 11.5px; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .doctor-block h2 { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div>
            Печать записей за {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}
            <span style="color:#6b7280;">· {{ $appointments->count() }} зап.</span>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('admin.appointments', ['date' => $date]) }}">← К записям</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Печать</button>
        </div>
    </div>

    <div class="sheet">
        <header class="header">
            <div>
                <h1>{{ config('app.name', 'Клиника') }}</h1>
                <div class="meta">Список записанных пациентов</div>
            </div>
            <div class="date-block">
                <strong>{{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('D MMMM YYYY') }}</strong>
                <span class="meta">{{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('dddd') }}</span>
            </div>
        </header>

        <div class="summary">
            <div>Всего записей: <strong>{{ $appointments->count() }}</strong></div>
            <div>Врачей: <strong>{{ $grouped->filter(fn ($g, $k) => $k != 0)->count() }}</strong></div>
            <div>Сформировано: {{ now()->format('d.m.Y H:i') }}</div>
        </div>

        @if($appointments->isEmpty())
            <div class="empty">На выбранную дату записей нет.</div>
        @else
            @foreach($grouped as $doctorId => $items)
                @php
                    $doctor = optional(optional($items->first())->schedule)->user;
                @endphp
                <section class="doctor-block">
                    <h2>
                        @if($doctor)
                            {{ $doctor->name }}
                            @if($doctor->specialization)
                                <span style="font-weight:400;color:#666;">— {{ $doctor->specialization }}</span>
                            @endif
                        @else
                            Без врача
                        @endif
                        <span style="float:right;font-weight:400;color:#666;">{{ $items->count() }}</span>
                    </h2>
                    <table>
                        <thead>
                            <tr>
                                <th style="width:70px;">№</th>
                                <th style="width:90px;">Время</th>
                                <th>Пациент</th>
                                <th style="width:130px;">ИИН</th>
                                <th style="width:120px;">Телефон</th>
                                <th>Услуга</th>
                                <th style="width:110px;">Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $appointment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="time">
                                        @if($appointment->appointment_time)
                                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                                            @if($appointment->appointment_end_time)
                                               –{{ \Carbon\Carbon::parse($appointment->appointment_end_time)->format('H:i') }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $appointment->client_name }}</td>
                                    <td>{{ $appointment->patient_iin ?: '—' }}</td>
                                    <td>{{ $appointment->client_phone }}</td>
                                    <td>{{ optional($appointment->service)->name ?? '—' }}</td>
                                    <td>{{ $statusLabels[$appointment->status] ?? $appointment->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endforeach
        @endif

        <footer class="footer">
            <span>{{ config('app.name') }}</span>
            <span>Страница для печати · {{ now()->format('d.m.Y H:i') }}</span>
        </footer>
    </div>

    <script>
        // Auto-open print dialog when opened specifically for printing
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 250);
            });
        }
    </script>
</body>
</html>
