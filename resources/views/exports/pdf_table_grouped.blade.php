<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7px;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2E5C8A;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 5px 0;
            color: #2E5C8A;
            font-size: 16px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            direction: rtl;
        }

        th,
        td {
            border: 0.5px solid #333;
            padding: 3px 4px;
            text-align: right;
            vertical-align: middle;
            word-wrap: break-word;
            direction: rtl;
        }

        /* Group Headers Row */
        .group-header {
            background-color: #2E5C8A;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 8px;
            padding: 5px 4px;
            text-align: center;
            direction: rtl;
        }

        /* Column Headers Row */
        .column-header {
            background-color: #D0E1F9;
            font-weight: bold;
            font-size: 7px;
            padding: 4px;
            text-align: right;
            direction: rtl;
        }

        /* Data Rows */
        tbody tr:nth-child(odd) {
            background-color: #FFFFFF;
        }

        tbody tr:nth-child(even) {
            background-color: #F8F9FA;
        }

        /* Page Break */
        .page-break {
            page-break-before: always;
        }

        .section-title {
            background-color: #2E5C8A;
            color: white;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>تاريخ الاستخراج: {{ now()->format('Y-m-d H:i') }}</p>
        @if(isset($totalRecords))
            <p>إجمالي السجلات: {{ $totalRecords }}</p>
        @endif
    </div>

    @if(isset($tables) && is_array($tables))
        {{-- Multiple tables (split mode) --}}
        @foreach($tables as $index => $tableData)
            @if($index > 0)
                <div class="page-break"></div>
            @endif
            
            @if(isset($tableData['section_title']))
                <div class="section-title">{{ $tableData['section_title'] }}</div>
            @endif

            <table>
                <thead>
                    {{-- Group Headers Row --}}
                    @if(isset($tableData['groupHeaders']))
                        <tr>
                            @foreach($tableData['groupHeaders'] as $groupHeader)
                                <th class="group-header" @if(isset($groupHeader['colspan'])) colspan="{{ $groupHeader['colspan'] }}" @endif>
                                    {{ $groupHeader['text'] ?? $groupHeader }}
                                </th>
                            @endforeach
                        </tr>
                    @endif

                    {{-- Column Headers Row --}}
                    <tr>
                        @foreach($tableData['columnHeaders'] as $heading)
                            <th class="column-header">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableData['rows'] as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{{ $cell ?? '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        {{-- Single table mode (backward compatible) --}}
        <table>
            <thead>
                {{-- Group Headers Row --}}
                @if(isset($groupHeaders))
                    <tr>
                        @foreach($groupHeaders as $groupHeader)
                            <th class="group-header" @if(isset($groupHeader['colspan'])) colspan="{{ $groupHeader['colspan'] }}" @endif>
                                {{ $groupHeader['text'] ?? $groupHeader }}
                            </th>
                        @endforeach
                    </tr>
                @endif

                {{-- Column Headers Row --}}
                <tr>
                    @foreach($headings as $heading)
                        <th class="column-header">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell ?? '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
