<table>
    <thead>
        <tr>
            <th colspan="3"
                style="font-weight: bold; font-size: 14pt; text-align: center; background-color: #2F5597; color: #FFFFFF; height: 25pt;">
                FORM PROFITABILITY ANALYSIS</th>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold; height: 20pt;">
                No : {{ $data['header']['document_number'] }}</td>
        </tr>
        <tr>
            <th colspan="3"></th>
        </tr>

        <!-- HEADER SECTION -->
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2; width: 50;">Account
                Manager &amp; Sales (AMS)</th>
            <td colspan="2" style="border: 1px solid #000000; padding-right: 5px; text-align: right;">
                {{ $data['header']['ams'] }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2;">Customer</th>
            <td colspan="2" style="border: 1px solid #000000; padding-right: 5px; text-align: right;">
                {{ $data['header']['customer'] }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2;">Project Name</th>
            <td colspan="2" style="border: 1px solid #000000; padding-right: 5px; text-align: right;">
                {{ $data['header']['project_name'] }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2;">Project Code</th>
            <td colspan="2" style="border: 1px solid #000000; padding-right: 5px; text-align: right;">
                {{ $data['header']['project_code'] }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2;">Revision</th>
            <td colspan="2" style="border: 1px solid #000000; padding-right: 5px; text-align: right;">
                {{ $data['header']['revision'] }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2;">Start Date</th>
            <td colspan="2" style="border: 1px solid #000000; padding-right: 5px; text-align: right;">
                {{ $data['header']['start_date'] }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2;">End Date</th>
            <td colspan="2" style="border: 1px solid #000000; padding-right: 5px; text-align: right;">
                {{ $data['header']['end_date'] }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2;">Term of Payment (ToP)
            </th>
            <td colspan="2" style="border: 1px solid #000000; padding-right: 5px; text-align: right;">
                {{ $data['header']['top'] }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #D9E1F2;">Remarks</th>
            <td colspan="2"
                style="border: 1px solid #000000; padding-right: 5px; text-align: right; font-weight: bold;">
                {{ $data['header']['remarks'] }}</td>
        </tr>

        <tr>
            <th colspan="3"></th>
        </tr>

        <tr>
            <th colspan="3" style="height: 15pt;"></th>
        </tr>
    </thead>
    <tbody>

        <!-- REVENUE SECTION -->
        <tr style="font-weight: bold;">
            <td>REVENUE</td>
            <td></td>
            <td></td>
        </tr>
        @foreach ($data['revenue']['items'] as $item)
            <tr>
                <td style="border: 1px solid #000000; padding-left: 15px; color: #333333;">
                    &nbsp;&nbsp;&nbsp;{{ $item['name'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; padding-right: 5px;">{{ $item['qty'] }}</td>
                <td style="border: 1px solid #000000; text-align: right;">{{ $item['amount'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight: bold; background-color: #E2EFDA;">
            <td style="border: 2px solid #000000; background-color: #E2EFDA;">Total Revenue (Nett per Month)</td>
            <td style="border: 2px solid #000000;"></td>
            <td style="border: 2px solid #000000; text-align: right;">{{ $data['revenue']['total'] }}</td>
        </tr>

        <tr>
            <td colspan="3"></td>
        </tr>

        <!-- DIRECT COST SECTION -->
        <tr style="font-weight: bold;">
            <td>DIRECT COST</td>
            <td></td>
            <td></td>
        </tr>

        <!-- MANPOWER -->
        @foreach ($data['direct_cost']['manpower'] as $key => $item)
            <tr>
                <td style="border: 1px solid #000000; padding-left: 15px; color: #333333;">
                    &nbsp;&nbsp;&nbsp;{{ $item['name'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; padding-right: 5px;">
                    {{ !empty($item['qty']) ? $item['qty'] : '' }}</td>
                <td style="border: 1px solid #000000; text-align: right;">{{ $item['amount'] }}</td>
            </tr>
        @endforeach

        <!-- OPERATIONAL -->
        @foreach ($data['direct_cost']['operational'] as $name => $item)
            <tr>
                <td style="border: 1px solid #000000; padding-left: 15px; color: #333333;">
                    &nbsp;&nbsp;&nbsp;{{ $name }}</td>
                <td style="border: 1px solid #000000; text-align: right; padding-right: 5px;">
                    {{ !empty($item['qty']) ? $item['qty'] : '' }}</td>
                <td style="border: 1px solid #000000; text-align: right;">{{ $item['amount'] }}</td>
            </tr>
        @endforeach

        <tr style="font-weight: bold; background-color: #FCE4D6;">
            <td style="border: 2px solid #000000; background-color: #FCE4D6;">Total Direct Cost</td>
            <td style="border: 2px solid #000000;"></td>
            <td style="border: 2px solid #000000; text-align: right;">{{ $data['direct_cost']['total'] }}</td>
        </tr>

        <tr>
            <td colspan="3"></td>
        </tr>

        <!-- GROSS PROFIT -->
        <tr style="font-weight: bold; background-color: #D9E1F2;">
            <td style="border: 2px solid #000000; background-color: #D9E1F2;">GROSS PROFIT</td>
            <td style="border: 2px solid #000000;"></td>
            <td style="border: 2px solid #000000; text-align: right;">{{ $data['gp']['amount'] }}</td>
        </tr>
        <tr style="font-weight: bold; background-color: #D9E1F2;">
            <td style="border: 2px solid #000000; background-color: #D9E1F2;">GROSS PROFIT MARGIN (%)</td>
            <td style="border: 2px solid #000000; text-align: right; padding-right: 5px;">
                {{ number_format($data['gp']['margin'], 2) }}%</td>
            <td style="border: 2px solid #000000;"></td>
        </tr>

        <tr>
            <td colspan="3"></td>
        </tr>

        <!-- INDIRECT COST SECTION -->
        <tr style="font-weight: bold;">
            <td>INDIRECT COST</td>
            <td></td>
            <td></td>
        </tr>
        @foreach ($data['indirect_cost']['categories'] as $name => $item)
            <tr>
                <td style="border: 1px solid #000000; padding-left: 15px; color: #333333;">
                    &nbsp;&nbsp;&nbsp;{{ $name }}</td>
                <td style="border: 1px solid #000000; text-align: center;"></td>
                <td style="border: 1px solid #000000; text-align: right;">{{ $item['amount'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight: bold; background-color: #D9D9D9;">
            <td style="border: 2px solid #000000; background-color: #D9D9D9;">Total Indirect Cost</td>
            <td style="border: 2px solid #000000;"></td>
            <td style="border: 2px solid #000000; text-align: right;">{{ $data['indirect_cost']['total'] }}</td>
        </tr>

        <tr>
            <td colspan="3"></td>
        </tr>

        <!-- FINANCIAL PERFORMANCE -->
        <tr style="font-weight: bold;">
            <td>FINANCIAL PERFORMANCE</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000000; padding-left: 15px;">&nbsp;&nbsp;&nbsp;EBITDA</td>
            <td style="border: 1px solid #000000;"></td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $data['financial']['ebitda'] }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000000; padding-left: 15px;">&nbsp;&nbsp;&nbsp;Depreciation &amp; Amortization
            </td>
            <td style="border: 1px solid #000000;"></td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $data['financial']['depreciation'] }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000000; padding-left: 15px;">&nbsp;&nbsp;&nbsp;EBIT</td>
            <td style="border: 1px solid #000000;"></td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $data['financial']['ebit'] }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000000; padding-left: 15px;">&nbsp;&nbsp;&nbsp;Interest or Cost of Fund</td>
            <td style="border: 1px solid #000000; text-align: right; padding-right: 5px;">
                {{ (float) $record->interest_rate }}%</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $data['financial']['interest'] }}</td>
        </tr>
        <tr style="background-color: #FFF2CC; font-weight: bold;">
            <td style="border: 1px solid #000000; padding-left: 15px; background-color: #FFF2CC;">&nbsp;&nbsp;&nbsp;EBT
            </td>
            <td style="border: 1px solid #000000;"></td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $data['financial']['ebt'] }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000000; padding-left: 15px;">&nbsp;&nbsp;&nbsp;Corporate Tax Income
                ({{ (float) $record->tax_rate }}%/bulan)</td>
            <td style="border: 1px solid #000000;"></td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $data['financial']['tax'] }}</td>
        </tr>
        <tr style="background-color: #FFF2CC; font-weight: bold;">
            <td style="border: 2px solid #000000; background-color: #FFF2CC;">NET PROFIT</td>
            <td style="border: 2px solid #000000;"></td>
            <td style="border: 2px solid #000000; text-align: right;">{{ $data['financial']['net_profit'] }}</td>
        </tr>
        <tr style="background-color: #FFF2CC; font-weight: bold;">
            <td style="border: 2px solid #000000; background-color: #FFF2CC;">NET PROFIT MARGIN (%)</td>
            <td style="border: 2px solid #000000;">
            </td>
            <td style="border: 2px solid #000000; text-align: right; padding-right: 5px;">
                {{ number_format($data['financial']['npm'], 2) }}%
            </td>
        </tr>

        <tr>
            <td colspan="3" style="height: 30pt;"></td>
        </tr>

        <!-- SIGNATURE SECTION -->
        <tr>
            <td colspan="3" style="height: 20pt;"></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: right; padding-right: 5px; font-style: italic;">
                Tangerang, {{ $data['signatures']['approver'][0]['date'] ?? now()->format('d M Y') }}
            </td>
        </tr>

        @php
            $stages = [
                ['label' => 'Review & Verification', 'items' => $data['signatures']['reviewer']],
                ['label' => 'Margin Authorization', 'items' => $data['signatures']['margin_approval']],
                ['label' => 'Final Approval', 'items' => $data['signatures']['approver']],
                ['label' => 'Acknowledgment', 'items' => $data['signatures']['acknowledger'] ?? []],
            ];
        @endphp

        @foreach ($stages as $stage)
            @if (!empty($stage['items']))
                <tr>
                    <td colspan="3" style="height: 15pt;"></td>
                </tr>
                <tr>
                    <td colspan="3" style="font-weight: bold; border-bottom: 1px solid #000000; background-color: #F8FAFC; height: 20pt; vertical-align: middle; padding-left: 5px;">
                        {{ strtoupper($stage['label']) }}
                    </td>
                </tr>
                
                {{-- Render items in chunks of 3 for horizontal layout --}}
                @foreach (array_chunk($stage['items'], 3) as $rowItems)
                    <tr>
                        @foreach ($rowItems as $item)
                            <td style="text-align: center; font-weight: bold; padding-top: 10px;">{{ $item['title'] }}</td>
                        @endforeach
                        {{-- Fill empty columns if less than 3 --}}
                        @for ($i = count($rowItems); $i < 3; $i++) <td></td> @endfor
                    </tr>
                    <tr>
                        @foreach ($rowItems as $item)
                            <td style="height: 50pt; text-align: center; vertical-align: middle; color: #64748b; font-size: 8pt;">
                                @if(!empty($item['qr_code']))
                                    (Signed Digitally)
                                @else
                                    (Pending Signature)
                                @endif
                            </td>
                        @endforeach
                        @for ($i = count($rowItems); $i < 3; $i++) <td></td> @endfor
                    </tr>
                    <tr>
                        @foreach ($rowItems as $item)
                            <td style="text-align: center; font-weight: bold; text-decoration: underline;">
                                {{ $item['name'] }}
                            </td>
                        @endforeach
                        @for ($i = count($rowItems); $i < 3; $i++) <td></td> @endfor
                    </tr>
                    <tr>
                        @foreach ($rowItems as $item)
                            <td style="text-align: center; font-size: 8pt; font-style: italic; padding-bottom: 10px;">
                                {{ $item['date'] !== '-' ? 'Signed on ' . $item['date'] : 'Pending Signature' }}
                            </td>
                        @endforeach
                        @for ($i = count($rowItems); $i < 3; $i++) <td></td> @endfor
                    </tr>
                @endforeach
            @endif
        @endforeach

    </tbody>
</table>
