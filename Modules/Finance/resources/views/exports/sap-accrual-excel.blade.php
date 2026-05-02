<table>
    <thead>
    <tr>
        <th>Document Date</th>
        <th>Document Type</th>
        <th>Company Code</th>
        <th>Posting Date</th>
        <th>Period</th>
        <th>Currency</th>
        <th>Exchange Rate</th>
        <th>Reference</th>
        <th>Header Text</th>
        <th>Posting Key</th>
        <th>Account Number</th>
        <th>Amount in Document Currency</th>
        <th>Amount in Local Currency</th>
        <th>Assignment</th>
        <th>Text</th>
        <th>Business Area</th>
        <th>Cost Centre</th>
        <th>Internal Order</th>
        <th>Profit Centre</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        {{-- Line 1: Debit (Accrued Revenue / Asset) --}}
        <tr>
            <td>{{ $row['doc_date'] }}</td>
            <td>{{ $row['doc_type'] }}</td>
            <td>{{ $row['company_code'] }}</td>
            <td>{{ $row['posting_date'] }}</td>
            <td>{{ $row['period'] }}</td>
            <td>{{ $row['currency'] }}</td>
            <td></td>
            <td>{{ $row['reference'] }}</td>
            <td>{{ $row['header_text'] }}</td>
            <td>40</td> {{-- Debit --}}
            <td>{{ $row['accrued_account'] }}</td>
            <td>{{ $row['amount'] }}</td>
            <td>{{ $row['amount'] }}</td>
            <td>{{ $row['assignment'] }}</td>
            <td>{{ $row['text'] }}</td>
            <td>{{ $row['business_area'] }}</td>
            <td>{{ $row['cost_center'] }}</td>
            <td></td>
            <td>{{ $row['profit_center'] }}</td>
        </tr>
        {{-- Line 2: Credit (Revenue / Income) --}}
        <tr>
            <td>{{ $row['doc_date'] }}</td>
            <td>{{ $row['doc_type'] }}</td>
            <td>{{ $row['company_code'] }}</td>
            <td>{{ $row['posting_date'] }}</td>
            <td>{{ $row['period'] }}</td>
            <td>{{ $row['currency'] }}</td>
            <td></td>
            <td>{{ $row['reference'] }}</td>
            <td>{{ $row['header_text'] }}</td>
            <td>50</td> {{-- Credit --}}
            <td>{{ $row['revenue_account'] }}</td>
            <td>{{ $row['amount'] }}</td>
            <td>{{ $row['amount'] }}</td>
            <td>{{ $row['assignment'] }}</td>
            <td>{{ $row['text'] }}</td>
            <td>{{ $row['business_area'] }}</td>
            <td>{{ $row['cost_center'] }}</td>
            <td></td>
            <td>{{ $row['profit_center'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
