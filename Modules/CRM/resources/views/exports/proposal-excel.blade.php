<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 14px; text-align: center;">PROPOSAL DETAILS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-weight: bold;">Proposal Number</td>
            <td>{{ $record->number }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Customer</td>
            <td>{{ $record->customer->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Work Scheme</td>
            <td>{{ $record->workScheme->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Amount</td>
            <td>{{ number_format($record->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Status</td>
            <td>{{ $record->status->value ?? $record->status }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Submission Date</td>
            <td>{{ $record->submission_date ? $record->submission_date->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; background-color: #f1f5f9;">DIGITAL APPROVALS</th>
        </tr>
        @if ($record->signatures && $record->signatures->isNotEmpty())
            @foreach ($record->signatures as $signature)
                <tr>
                    <td style="font-weight: bold;">{{ $signature->role ?? 'Signer' }}</td>
                    <td>{{ $signature->user->name ?? 'Unknown' }} ({{ $signature->signed_at->format('d M Y H:i') }})
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2">No digital signatures recorded.</td>
            </tr>
        @endif
    </tbody>
</table>
