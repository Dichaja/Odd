<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Gross-Up Calculator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Gross-Up Calculator</h1>
        <form id="calcForm" class="space-y-4">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Desired Amount (A)</label>
                <input type="number" id="amount" name="amount" min="0" step="any"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter amount you want to receive (e.g. 100000)">
            </div>

            <div>
                <label for="rate" class="block text-sm font-medium text-gray-700">Commission Rate (%)</label>
                <input type="number" id="rate" name="rate" min="0" step="any"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter commission rate (e.g. 3)">
            </div>

            <div class="pt-4">
                <p class="text-lg font-semibold text-gray-700">Results:</p>
                <p class="mt-2 text-gray-700">
                    <span class="font-medium">Grossed-Up Amount (P):</span> <span id="grossed"
                        class="text-blue-600">--</span>
                </p>
                <p class="mt-1 text-gray-700">
                    <span class="font-medium">Extra Charge (P - A):</span> <span id="extra"
                        class="text-red-600">--</span>
                </p>
            </div>
        </form>
    </div>

    <script>
        const amountInput = document.getElementById('amount');
        const rateInput = document.getElementById('rate');
        const grossedOutput = document.getElementById('grossed');
        const extraOutput = document.getElementById('extra');

        function updateCalculation() {
            const A = parseFloat(amountInput.value);
            const r = parseFloat(rateInput.value);

            if (!isNaN(A) && !isNaN(r) && r < 100) {
                const rateDecimal = r / 100;
                const P = A / (1 - rateDecimal);
                const grossed = Math.ceil(P);
                const extra = grossed - A;

                grossedOutput.textContent = grossed.toLocaleString('en-UG', { style: 'currency', currency: 'UGX' });
                extraOutput.textContent = extra.toLocaleString('en-UG', { style: 'currency', currency: 'UGX' });
            } else {
                grossedOutput.textContent = '--';
                extraOutput.textContent = '--';
            }
        }

        amountInput.addEventListener('input', updateCalculation);
        rateInput.addEventListener('input', updateCalculation);
    </script>
</body>

</html>