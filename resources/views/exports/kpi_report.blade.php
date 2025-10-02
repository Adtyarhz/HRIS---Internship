<table>
    <thead>
        <tr>
            <th>Nama Karyawan</th>
            <th>Divisi</th>
            <th>Posisi</th>
            <th>Periode</th>
            <th>Supervisor</th>
            <th>Status</th>
            <th>Final Score</th>
        </tr>
    </thead>
    <tbody>
        @foreach($assessments as $a)
            <tr>
                <td>{{ $a->employee->full_name }}</td>
                <td>{{ $a->employee->division->name ?? '-' }}</td>
                <td>{{ $a->employee->position->title ?? '-' }}</td>
                <td>{{ $a->period->period_name ?? '-' }}</td>
                <td>{{ $a->supervisor->name ?? '-' }}</td>
                <td>{{ $a->status }}</td>
                <td>{{ $a->final_score }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
