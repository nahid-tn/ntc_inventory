<!DOCTYPE html>
<html lang="bn">

<head>

    <meta charset="UTF-8">

    <title>
        Old Product Exchange
        #{{ $exchange->id }}
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h2 {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table,
        th,
        td {
            border: 1px solid #333;
        }

        th,
        td {
            padding: 10px;
        }

        th {
            background: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .pay {
            color: #b00020;
            font-weight: bold;
            font-size: 18px;
        }

        .receive {
            color: #008000;
            font-weight: bold;
            font-size: 18px;
        }

        .no-adjustment {
            font-weight: bold;
            font-size: 18px;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        @media print {

            .no-print {
                display: none;
            }

            body {
                margin: 10px;
            }

        }

    </style>

</head>


<body>


<div class="no-print"
     style="text-align:right;">

    <button onclick="window.print()">
        Print
    </button>

</div>


<div class="header">

    <h2>
        OLD PRODUCT EXCHANGE
    </h2>

    <div>
        Exchange No:
        #{{ $exchange->id }}
    </div>

    <div>
        Date:
        {{ $exchange->exchange_date->format('d-m-Y') }}
    </div>

</div>


<table>

    <tr>

        <th>
            Customer Gives
        </th>

        <th>
            Customer Takes
        </th>

    </tr>


    <tr>

        <td>

            <strong>
                {{ $exchange->old_product_name }}
            </strong>

            <br><br>

            Quantity:
            {{ number_format(
                $exchange->old_qty,
                2
            ) }}
            {{ $exchange->old_unit }}

            <br>

            Price:
            {{ number_format(
                $exchange->old_price,
                2
            ) }}

            <br>

            Total:
            {{ number_format(
                $exchange->old_total,
                2
            ) }}

            টাকা

        </td>


        <td>

            <strong>
                {{ $exchange->exchange_product_name }}
            </strong>

            <br><br>

            Quantity:
            {{ number_format(
                $exchange->exchange_qty,
                2
            ) }}
            {{ $exchange->exchange_unit }}

            <br>

            Price:
            {{ number_format(
                $exchange->exchange_price,
                2
            ) }}

            <br>

            Total:
            {{ number_format(
                $exchange->exchange_total,
                2
            ) }}

            টাকা

        </td>

    </tr>

</table>


<table>

    <tr>

        <th>
            Description
        </th>

        <th class="text-right">
            Amount
        </th>

    </tr>


    <tr>

        <td>
            Customer Given Product Value
        </td>

        <td class="text-right">

            {{ number_format(
                $exchange->old_total,
                2
            ) }}

            টাকা

        </td>

    </tr>


    <tr>

        <td>
            Exchange Product Value
        </td>

        <td class="text-right">

            {{ number_format(
                $exchange->exchange_total,
                2
            ) }}

            টাকা

        </td>

    </tr>


    <tr>

        <td>
            Value Difference
        </td>

        <td class="text-right">

            {{ number_format(
                $exchange->value_difference,
                2
            ) }}

            টাকা

        </td>

    </tr>


    <tr>

        <td>
            Exchange Fee
        </td>

        <td class="text-right">

            {{ number_format(
                $exchange->exchange_fee,
                2
            ) }}

            টাকা

        </td>

    </tr>


    <tr>

        <th>
            Final Settlement
        </th>

        <th class="text-right">

            @if(
                $exchange->settlement_type
                === 'customer_pay'
            )

                <span class="pay">

                    Customer Pay:
                    {{ number_format(
                        abs(
                            $exchange
                                ->settlement_amount
                        ),
                        2
                    ) }}
                    টাকা

                </span>

            @elseif(
                $exchange->settlement_type
                === 'customer_receive'
            )

                <span class="receive">

                    Customer Receive:
                    {{ number_format(
                        abs(
                            $exchange
                                ->settlement_amount
                        ),
                        2
                    ) }}
                    টাকা

                </span>

            @else

                <span class="no-adjustment">

                    No Adjustment

                </span>

            @endif

        </th>

    </tr>

</table>


@if($exchange->notes)

    <p>

        <strong>
            Notes:
        </strong>

        {{ $exchange->notes }}

    </p>

@endif


<div class="footer">

    <div>
        Customer Signature
    </div>

    <div>
        Authorized Signature
    </div>

</div>


</body>

</html>