<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'SMS Center';
$activeNav = 'sms-center';
ob_start();
function formatCurrency($amount)
{
    return number_format((float) $amount, 2);
}
?>
<div class="min-h-screen" id="sms-app">
    <style>
        :root {
            --ui-bg: #ffffff;
            --ui-bg-sub: #f5f7fb;
            --ui-border: #e5e7eb;
            --ui-text: #111827;
            --ui-muted: #6b7280;
            --ui-primary: #D92B13;
            --ui-primary-50: #fde6e3;
            --ui-success: #16a34a;
            --ui-danger: #dc2626;
            --ui-warning: #f59e0b;
            --ui-blue: #2563eb;
            --ui-card-shadow: 0 1px 2px rgba(0, 0, 0, .06);
            --chip-bg: #f3f4f6;
            --chip-text: #111827
        }

        .dark :root,
        .dark {
            --ui-bg: #1a1a1a;
            --ui-bg-sub: #121212;
            --ui-border: rgba(255, 255, 255, .12);
            --ui-text: #f9fafb;
            --ui-muted: rgba(255, 255, 255, .7);
            --ui-card-shadow: none;
            --chip-bg: rgba(255, 255, 255, .08);
            --chip-text: #fff
        }

        .ui-card {
            background: var(--ui-bg);
            border: 1px solid var(--ui-border);
            border-radius: 14px;
            box-shadow: var(--ui-card-shadow)
        }

        .ui-head {
            background: var(--ui-bg);
            border-bottom: 1px solid var(--ui-border)
        }

        .ui-input,
        .ui-select,
        .ui-textarea {
            width: 100%;
            border: 1px solid var(--ui-border);
            border-radius: 12px;
            background: var(--ui-bg);
            color: var(--ui-text);
            padding: .8rem 1rem;
            outline: 0
        }

        .ui-textarea {
            min-height: 130px;
            resize: vertical
        }

        .ui-input::placeholder,
        .ui-textarea::placeholder {
            color: var(--ui-muted)
        }

        .ui-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1rem;
            border-radius: 12px;
            border: 1.5px solid var(--ui-border);
            cursor: pointer;
            user-select: none
        }

        .ui-pill.sel {
            border-color: var(--ui-primary);
            background: color-mix(in srgb, var(--ui-primary) 8%, transparent)
        }

        .ui-kpi {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--ui-border);
            border-radius: 14px;
            background: var(--ui-bg)
        }

        .ui-kpi .h {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--ui-muted);
            letter-spacing: .04em
        }

        .ui-kpi .v {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--ui-text);
            white-space: nowrap
        }

        .ui-grid {
            display: grid;
            gap: 1rem
        }

        @media(min-width:768px) {
            .ui-grid.cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .tab-btn {
            border-bottom: 2px solid transparent;
            padding: .9rem .25rem;
            font-weight: 600;
            color: var(--ui-muted)
        }

        .tab-btn.active {
            border-color: var(--ui-primary);
            color: var(--ui-primary)
        }

        .tab-btn:hover {
            color: var(--ui-primary)
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border-radius: 12px;
            padding: .8rem 1rem;
            font-weight: 600
        }

        .btn-primary {
            background: var(--ui-primary);
            color: #fff
        }

        .btn-primary:disabled {
            opacity: .6;
            cursor: not-allowed
        }

        .btn-light {
            background: var(--ui-bg);
            border: 1px solid var(--ui-border);
            color: var(--ui-text)
        }

        .btn-danger {
            background: var(--ui-danger);
            color: #fff
        }

        .btn-ghost {
            background: transparent;
            color: var(--ui-text)
        }

        .stat-box {
            display: grid;
            gap: .35rem;
            font-size: .9rem;
            color: var(--ui-text)
        }

        .stat-box .row {
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .stat-box .label {
            color: var(--ui-muted)
        }

        .chip {
            background: var(--chip-bg);
            color: var(--chip-text);
            border-radius: 999px;
            padding: .35rem .7rem;
            font-size: .8rem;
            cursor: pointer
        }

        .line-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden
        }

        .line-3 {
            -webkit-line-clamp: 3;
            line-clamp: 3;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0
        }

        .table thead th {
            position: sticky;
            top: 0;
            background: var(--ui-bg-sub);
            border-bottom: 1px solid var(--ui-border);
            color: var(--ui-muted);
            font-size: .75rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: .9rem .75rem;
            text-align: left
        }

        .table tbody td {
            border-bottom: 1px solid var(--ui-border);
            padding: .9rem .75rem;
            vertical-align: top
        }

        .row-hover {
            cursor: pointer
        }

        .row-hover:hover {
            background: color-mix(in srgb, var(--ui-primary) 6%, transparent)
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .75rem;
            padding: .25rem .55rem;
            border-radius: 999px
        }

        .b-sent {
            background: rgba(22, 163, 74, .12);
            color: #16a34a
        }

        .b-scheduled {
            background: rgba(37, 99, 235, .12);
            color: #2563eb
        }

        .b-failed {
            background: rgba(220, 38, 38, .12);
            color: #dc2626
        }

        .b-cancelled {
            background: rgba(100, 116, 139, .18);
            color: #64748b
        }

        .grid-2 {
            display: grid;
            gap: .9rem;
            grid-template-columns: 1fr
        }

        @media(min-width:640px) {
            .grid-2 {
                grid-template-columns: 1fr 1fr
            }
        }

        .shadow-sheet {
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25)
        }

        .ellipsis {
            max-width: 52ch;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }
    </style>

    <div class="ui-head px-4 sm:px-6 lg:px-8 py-5">
        <div class="max-w-7xl mx-auto ui-grid cols-3">
            <div class="ui-kpi">
                <div>
                    <div class="h">SMS Credit</div>
                    <div class="v" id="sms-credit-count">0</div>
                    <div class="text-[.85rem] font-medium" style="color:var(--ui-muted)">Messages Available</div>
                </div>
                <div class="w-10 h-10 rounded-lg grid place-items-center"
                    style="background:color-mix(in srgb,var(--ui-blue) 15%,transparent);color:var(--ui-blue)"><i
                        class="fas fa-sms"></i></div>
            </div>
            <div class="ui-kpi">
                <div>
                    <div class="h">Sent Today</div>
                    <div class="v" id="sent-today-count">0</div>
                    <div class="text-[.85rem] font-medium" style="color:var(--ui-muted)" id="sent-today-cost">Sh. 0.00
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg grid place-items-center"
                    style="background:color-mix(in srgb,var(--ui-success) 15%,transparent);color:var(--ui-success)"><i
                        class="fas fa-paper-plane"></i></div>
            </div>
            <div class="ui-kpi">
                <div>
                    <div class="h">Scheduled</div>
                    <div class="v" id="scheduled-count">0</div>
                    <div class="text-[.85rem] font-medium" style="color:var(--ui-muted)">Pending Messages</div>
                </div>
                <div class="w-10 h-10 rounded-lg grid place-items-center"
                    style="background:color-mix(in srgb,rebeccapurple 20%,transparent);color:rebeccapurple"><i
                        class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6">
        <div class="ui-card mb-6">
            <div class="d-flex gap-3 overflow-auto !p-[0.5rem]">
                <button id="send-tab" class="tab-btn active" onclick="switchTab('send')"><i
                        class="fas fa-paper-plane mr-2"></i>Send</button>
                <button id="history-tab" class="tab-btn" onclick="switchTab('history')"><i
                        class="fas fa-history mr-2"></i>History</button>
                <button id="templates-tab" class="tab-btn" onclick="switchTab('templates')"><i
                        class="fas fa-file-alt mr-2"></i>Templates</button>
                <button id="topup-tab" class="tab-btn" onclick="switchTab('topup')"><i
                        class="fas fa-plus-circle mr-2"></i>Top Up</button>
            </div>
        </div>

        <div id="tab-content">
            <div id="send-content" class="ui-card p-5">
                <form id="sms-form" class="grid gap-6">
                    <div class="grid-2">
                        <div class="grid gap-2">
                            <div class="text-sm font-semibold" style="color:var(--ui-text)">Send Type</div>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="ui-pill sel" data-sendtype="single"><input type="radio" name="sendType"
                                        value="single" class="sr-only" checked><span>Single</span></label>
                                <label class="ui-pill" data-sendtype="bulk"><input type="radio" name="sendType"
                                        value="bulk" class="sr-only"><span>Bulk</span></label>
                            </div>
                        </div>
                        <div class="ui-card p-4">
                            <div class="stat-box">
                                <div class="row"><span class="label">Recipients</span><span
                                        id="recipient-count">0</span></div>
                                <div class="row"><span class="label">Credits Needed</span><span
                                        id="credits-needed">0</span></div>
                                <div class="row"><span class="label">Estimated Cost</span><span id="estimated-cost">Sh.
                                        0.00</span></div>
                            </div>
                        </div>
                    </div>

                    <div id="single-recipient">
                        <div class="text-sm font-semibold" style="color:var(--ui-text)">Recipient</div>
                        <input type="tel" id="recipient" class="ui-input" placeholder="0700123456" autocomplete="off">
                    </div>

                    <div id="bulk-recipients" class="hidden grid gap-3">
                        <div class="text-sm font-semibold" style="color:var(--ui-text)">Recipients</div>
                        <div class="grid gap-2 sm:grid-cols-[1fr_auto]">
                            <input type="tel" id="bulk-number-input" class="ui-input"
                                placeholder="700123456 or 0700123456">
                            <button type="button" id="add-number-btn" class="btn btn-primary"><i
                                    class="fas fa-plus"></i>Add</button>
                        </div>
                        <div class="flex flex-wrap gap-2" id="recipient-tags"></div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <button type="button" onclick="pasteCsvPrompt()" class="btn btn-light"><i
                                    class="fas fa-paste"></i>Paste CSV</button>
                            <div>
                                <input type="file" id="bulk-upload-file" accept=".csv" class="hidden"
                                    onchange="handleBulkUpload(event)">
                                <button type="button" onclick="document.getElementById('bulk-upload-file').click()"
                                    class="btn btn-light w-full"><i class="fas fa-upload"></i>Upload CSV</button>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold" style="color:var(--ui-text)">Message</div>
                            <div class="text-xs" id="char-count" style="color:var(--ui-muted)">0/160 • 1 part(s)</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="chip" data-token="{name}">{name}</span>
                            <span class="chip" data-token="{order}">{order}</span>
                            <span class="chip" data-token="{amount}">{amount}</span>
                            <span class="chip" data-token="{date}">{date}</span>
                            <span class="chip" data-token="{store}">{store}</span>
                            <span class="chip" data-token="{otp}">{otp}</span>
                        </div>
                        <textarea id="message" class="ui-textarea" placeholder="Type your message..."
                            oninput="updateCharCount()"></textarea>
                        <div class="text-xs" style="color:var(--ui-muted)">Long messages split automatically</div>
                        <div><button type="button" class="btn btn-ghost" onclick="showTemplateSelector()"><i
                                    class="fas fa-magnet"></i>Use Template</button></div>
                    </div>

                    <div class="grid-2">
                        <div class="grid gap-2">
                            <div class="text-sm font-semibold" style="color:var(--ui-text)">Send Options</div>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="ui-pill sel" data-sendopt="now"><input type="radio" name="sendOption"
                                        value="now" class="sr-only" checked><span>Send Now</span></label>
                                <label class="ui-pill" data-sendopt="schedule"><input type="radio" name="sendOption"
                                        value="schedule" class="sr-only"><span>Schedule</span></label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2" id="schedule-options" style="display:none">
                            <input type="date" id="schedule-date" class="ui-input" placeholder="yyyy-mm-dd">
                            <input type="time" id="schedule-time" class="ui-input">
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <button type="submit" id="send-sms-btn" class="btn btn-primary w-full"><i
                                class="fas fa-paper-plane"></i><span id="send-button-text">Send SMS</span></button>
                    </div>
                </form>
            </div>

            <div id="history-content" class="hidden grid gap-4">
                <div class="ui-card p-5 grid gap-4 lg:grid-cols-[1fr_auto_auto_auto]">
                    <div class="relative">
                        <input type="text" id="searchHistory" class="ui-input" placeholder="Search SMS history..."
                            oninput="filterHistory()">
                    </div>
                    <select id="statusFilter" class="ui-select" onchange="filterHistory()">
                        <option value="">All Status</option>
                        <option value="sent">Sent</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="failed">Failed</option>
                    </select>
                    <input type="date" id="dateFromFilter" class="ui-input" onchange="filterHistory()">
                    <input type="date" id="dateToFilter" class="ui-input" onchange="filterHistory()">
                </div>

                <div class="ui-card overflow-hidden">
                    <div class="hidden lg:block max-h-[70vh] overflow-auto">
                        <table class="table" id="history-table">
                            <thead>
                                <tr>
                                    <th>Message</th>
                                    <th>Recipients</th>
                                    <th>Status</th>
                                    <th>Cost</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body"></tbody>
                        </table>
                    </div>
                    <div class="lg:hidden p-4 space-y-3 max-h-[70vh] overflow-auto" id="history-mobile"></div>
                    <div id="history-empty-state" class="text-center py-16 hidden">
                        <div class="w-20 h-20 rounded-full grid place-items-center mx-auto mb-3"
                            style="background:var(--ui-bg-sub)"><i class="fas fa-history"
                                style="color:var(--ui-muted)"></i></div>
                        <div class="text-lg font-semibold" style="color:var(--ui-text)">No SMS history</div>
                        <div style="color:var(--ui-muted)">Send your first SMS to see it here</div>
                        <div class="mt-4"><button class="btn btn-primary" onclick="switchTab('send')">Send SMS</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="templates-content" class="hidden grid gap-4">
                <div class="ui-card p-5 grid gap-4 lg:grid-cols-[1fr_auto]">
                    <input type="text" id="searchTemplates" class="ui-input" placeholder="Search templates..."
                        oninput="filterTemplates()">
                    <button onclick="showCreateTemplateForm()" class="btn btn-primary"><i class="fas fa-plus"></i>Create
                        Template</button>
                </div>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" id="templates-grid"></div>
                <div id="templates-empty-state" class="hidden text-center py-16">
                    <div class="w-20 h-20 rounded-full grid place-items-center mx-auto mb-3"
                        style="background:var(--ui-bg-sub)"><i class="fas fa-file-alt"
                            style="color:var(--ui-muted)"></i></div>
                    <div class="text-lg font-semibold" style="color:var(--ui-text)">No templates</div>
                    <div style="color:var(--ui-muted)">Create your first template to save time</div>
                    <div class="mt-4"><button class="btn btn-primary" onclick="showCreateTemplateForm()">Create
                            Template</button></div>
                </div>
            </div>

            <div id="topup-content" class="hidden max-w-2xl mx-auto ui-card p-6 grid gap-6">
                <div class="ui-card p-5 grid place-items-center">
                    <div class="text-sm" style="color:var(--ui-muted)">Current SMS Rate</div>
                    <div class="text-3xl font-extrabold" id="topup-sms-rate" style="color:var(--ui-text)">Sh. 0.00</div>
                    <div class="text-xs" style="color:var(--ui-muted)">per SMS</div>
                </div>
                <div class="ui-card p-5 grid gap-3">
                    <div class="font-semibold" style="color:var(--ui-text)">Wallet</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="text-sm" style="color:var(--ui-muted)">Balance</div>
                            <div class="text-2xl font-bold" id="wallet-balance" style="color:var(--ui-text)">Sh. 0.00
                            </div>
                        </div>
                        <div>
                            <div class="text-sm" style="color:var(--ui-muted)">Equivalent Credits</div>
                            <div class="text-2xl font-bold" id="wallet-credits" style="color:var(--ui-blue)">0</div>
                        </div>
                    </div>
                </div>
                <form id="topup-form" class="grid gap-5">
                    <div class="grid gap-2">
                        <div class="text-sm font-semibold" style="color:var(--ui-text)">Select Package</div>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="ui-pill pkg" data-pkg="100"><input type="radio" name="package" value="100"
                                    class="sr-only">
                                <div class="flex-1">
                                    <div class="font-semibold">100 SMS</div>
                                    <div class="text-sm" style="color:var(--ui-muted)" id="package-100-cost">
                                        Calculating...</div>
                                </div>
                            </label>
                            <label class="ui-pill pkg" data-pkg="500"><input type="radio" name="package" value="500"
                                    class="sr-only">
                                <div class="flex-1">
                                    <div class="font-semibold">500 SMS</div>
                                    <div class="text-sm" style="color:var(--ui-muted)" id="package-500-cost">
                                        Calculating...</div>
                                </div>
                            </label>
                            <label class="ui-pill pkg" data-pkg="1000"><input type="radio" name="package" value="1000"
                                    class="sr-only">
                                <div class="flex-1">
                                    <div class="font-semibold">1,000 SMS</div>
                                    <div class="text-sm" style="color:var(--ui-muted)" id="package-1000-cost">
                                        Calculating...</div>
                                </div>
                            </label>
                            <label class="ui-pill pkg" data-pkg="custom"><input type="radio" name="package"
                                    value="custom" class="sr-only">
                                <div class="flex-1">
                                    <div class="font-semibold">Custom</div>
                                    <div class="text-sm" style="color:var(--ui-muted)">Enter amount</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div id="custom-amount-section" class="hidden grid gap-2">
                        <div class="text-sm font-semibold" style="color:var(--ui-text)">Custom Amount (Sh.)</div>
                        <input type="number" id="custom-amount" class="ui-input" placeholder="Enter amount" min="1000"
                            step="100" oninput="updateTopupCalculation()">
                    </div>
                    <div class="ui-card p-4 grid gap-2">
                        <div class="row flex items-center justify-between"><span style="color:var(--ui-muted)">SMS
                                Credits</span><span id="topup-credits" class="font-semibold">0</span></div>
                        <div class="row flex items-center justify-between"><span style="color:var(--ui-muted)">Total
                                Cost</span><span id="topup-cost" class="font-semibold">Sh. 0.00</span></div>
                        <div class="row flex items-center justify-between"><span style="color:var(--ui-muted)">Wallet
                                Balance</span><span id="balance-status" class="font-semibold">Sh. 0.00</span></div>
                    </div>
                    <div id="insufficient-balance-warning" class="hidden ui-card p-4"
                        style="background:rgba(220,38,38,.06);border-color:rgba(220,38,38,.3)">
                        <div class="font-semibold" style="color:var(--ui-danger)">Insufficient Wallet Balance</div>
                        <div class="text-sm" style="color:var(--ui-danger)">Top up your wallet before purchasing
                            credits.</div>
                    </div>
                    <button type="submit" id="purchase-credits-btn" class="btn btn-primary w-full"><i
                            class="fas fa-credit-card"></i>Purchase Credits</button>
                </form>
            </div>
        </div>
    </div>

    <div id="bulkUploadModal" class="fixed inset-0 z-50 hidden p-4">
        <div class="absolute inset-0 bg-black/50" onclick="hideBulkUploadModal()"></div>
        <div class="ui-card shadow-sheet max-w-2xl mx-auto relative z-10 max-h-[80vh] overflow-hidden">
            <div class="ui-head px-5 py-4 flex items-center justify-between">
                <div class="font-semibold">Bulk Upload Results</div><button class="btn btn-light"
                    onclick="hideBulkUploadModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-5 overflow-y-auto max-h-[60vh]">
                <div id="upload-results-content"></div>
            </div>
            <div class="p-5"><button type="button" onclick="hideBulkUploadModal()"
                    class="btn btn-primary w-full">Continue</button></div>
        </div>
    </div>

    <div id="templateSelectorModal" class="fixed inset-0 z-50 hidden p-4">
        <div class="absolute inset-0 bg-black/50" onclick="hideTemplateSelector()"></div>
        <div class="ui-card shadow-sheet max-w-2xl mx-auto relative z-10 max-h-[80vh] overflow-hidden">
            <div class="ui-head px-5 py-4 flex items-center justify-between">
                <div class="font-semibold">Select Template</div><button class="btn btn-light"
                    onclick="hideTemplateSelector()"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-5 overflow-y-auto max-h-[60vh]">
                <div class="grid gap-2" id="template-selector-list"></div>
            </div>
        </div>
    </div>

    <div id="templateModal" class="fixed inset-0 z-50 hidden p-4">
        <div class="absolute inset-0 bg-black/50" onclick="hideTemplateModal()"></div>
        <div class="ui-card shadow-sheet max-w-lg mx-auto relative z-10">
            <div class="ui-head px-5 py-4 flex items-center justify-between">
                <div class="font-semibold" id="template-modal-title">Create Template</div><button class="btn btn-light"
                    onclick="hideTemplateModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-5 grid gap-4">
                <form id="template-form" class="grid gap-4">
                    <input type="hidden" id="template-id">
                    <div class="grid gap-1">
                        <div class="text-sm font-semibold" style="color:var(--ui-text)">Template Name</div>
                        <input type="text" id="template-name" class="ui-input" placeholder="Enter template name"
                            required>
                    </div>
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold" style="color:var(--ui-text)">Message</div>
                            <div class="text-xs" id="template-char-count" style="color:var(--ui-muted)">0/160</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="chip tchip" data-token="{name}">{name}</span>
                            <span class="chip tchip" data-token="{order}">{order}</span>
                            <span class="chip tchip" data-token="{amount}">{amount}</span>
                            <span class="chip tchip" data-token="{date}">{date}</span>
                            <span class="chip tchip" data-token="{store}">{store}</span>
                            <span class="chip tchip" data-token="{otp}">{otp}</span>
                        </div>
                        <textarea id="template-message" class="ui-textarea" placeholder="Enter template message"
                            required oninput="updateTemplateCharCount()"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" class="btn btn-light" onclick="hideTemplateModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="messageModal" class="fixed inset-0 z-50 hidden p-4">
        <div class="absolute inset-0 bg-black/50" onclick="hideMessageModal()"></div>
        <div class="ui-card shadow-sheet max-w-md mx-auto relative z-10">
            <div class="ui-head px-5 py-4 flex items-center justify-between">
                <div class="font-semibold" id="message-modal-title">Message</div><button class="btn btn-light"
                    onclick="hideMessageModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-5 grid gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg grid place-items-center" id="message-modal-icon"></div>
                    <div id="message-modal-text" class="text-sm"></div>
                </div>
                <button class="btn btn-primary w-full" onclick="hideMessageModal()">OK</button>
            </div>
        </div>
    </div>

    <div id="confirmationModal" class="fixed inset-0 z-50 hidden p-4">
        <div class="absolute inset-0 bg-black/50" onclick="hideConfirmationModal()"></div>
        <div class="ui-card shadow-sheet max-w-lg mx-auto relative z-10">
            <div class="ui-head px-5 py-4 flex items-center justify-between">
                <div class="font-semibold">Confirm SMS Details</div><button class="btn btn-light"
                    onclick="hideConfirmationModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-5">
                <div class="grid gap-2 text-sm" id="confirmation-details"></div>
            </div>
            <div class="p-5 grid grid-cols-2 gap-3">
                <button class="btn btn-light" onclick="hideConfirmationModal()">Cancel</button>
                <button class="btn btn-primary" onclick="confirmSendSms()"><i class="fas fa-paper-plane"></i>Send
                    SMS</button>
            </div>
        </div>
    </div>
</div>

<script>
    let smsStats = {}, smsHistory = [], smsTemplates = [], currentTab = 'send', bulkRecipients = [], currentSmsRate = 0, walletBalance = 0, walletCredits = 0;
    document.addEventListener('DOMContentLoaded', () => { wireUI(); switchTab('send'); updateCharCount(); loadAll(); });

    function api(action, data = {}, method = 'POST') {
        const url = 'fetch/manageSmsCenter.php';
        if (method === 'GET') {
            const p = new URLSearchParams({ action, ...data });
            return fetch(`${url}?${p.toString()}`).then(r => r.json());
        } else {
            const fd = new FormData(); fd.append('action', action);
            Object.keys(data).forEach(k => { if (data[k] !== undefined && data[k] !== null) fd.append(k, data[k]); });
            return fetch(url, { method: 'POST', body: fd }).then(r => r.json());
        }
    }

    function loadAll() {
        Promise.all([api('getSmsStats', '', 'POST'), api('getWalletBalance', '', 'POST')]).then(([s, w]) => {
            if (s?.success) {
                smsStats = s.data || {};
                document.getElementById('sms-credit-count').textContent = smsStats.current_credits || 0;
                document.getElementById('sent-today-count').textContent = smsStats.sent_today || 0;
                document.getElementById('sent-today-cost').textContent = `Sh. ${formatCurrency(smsStats.sent_today_cost || 0)}`;
                document.getElementById('scheduled-count').textContent = smsStats.scheduled_count || 0;
            }
            if (w?.success) {
                walletBalance = w.data.balance || 0;
                currentSmsRate = w.data.sms_rate || 0;
                walletCredits = w.data.equivalent_credits || 0;
                document.getElementById('topup-sms-rate').textContent = `Sh. ${formatCurrency(currentSmsRate)}`;
                document.getElementById('wallet-balance').textContent = `Sh. ${formatCurrency(walletBalance)}`;
                document.getElementById('wallet-credits').textContent = walletCredits;
                updatePackageCosts(); updateTopupCalculation();
            }
        });
        loadSmsHistory();
        loadSmsTemplates();
    }

    function wireUI() {
        document.getElementById('sms-form').addEventListener('submit', handleSendSms);
        document.getElementById('topup-form').addEventListener('submit', handlePurchaseCredits);
        document.getElementById('template-form').addEventListener('submit', handleSaveTemplate);
        document.getElementById('recipient').addEventListener('input', updateSendFormCalculations);
        document.getElementById('bulk-number-input').addEventListener('keypress', e => { if (e.key === 'Enter') { e.preventDefault(); addBulkRecipient(); } });
        document.querySelectorAll('[data-sendtype]').forEach(el => el.addEventListener('click', () => { document.querySelectorAll('[data-sendtype]').forEach(x => x.classList.remove('sel')); el.classList.add('sel'); el.querySelector('input').checked = true; toggleSendType(); }));
        document.querySelectorAll('[data-sendopt]').forEach(el => el.addEventListener('click', () => { document.querySelectorAll('[data-sendopt]').forEach(x => x.classList.remove('sel')); el.classList.add('sel'); el.querySelector('input').checked = true; toggleSchedule(); }));
        document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', () => insertAtCursor(document.getElementById('message'), c.dataset.token)));
        document.querySelectorAll('.tchip').forEach(c => c.addEventListener('click', () => insertAtCursor(document.getElementById('template-message'), c.dataset.token)));
        document.querySelectorAll('.pkg').forEach(p => p.addEventListener('click', () => { document.querySelectorAll('.pkg').forEach(x => x.classList.remove('sel')); p.classList.add('sel'); p.querySelector('input').checked = true; updateTopupCalculation(); }));
        setDefaultDateFilters();
    }

    function switchTab(tab) { ['send', 'history', 'templates', 'topup'].forEach(t => { document.getElementById(`${t}-content`).classList.add('hidden'); document.getElementById(`${t}-tab`).classList.remove('active') }); document.getElementById(`${tab}-content`).classList.remove('hidden'); document.getElementById(`${tab}-tab`).classList.add('active'); currentTab = tab; if (tab === 'history') { setDefaultDateFilters(); loadSmsHistory(); } if (tab === 'templates') { loadSmsTemplates(); } }

    function setDefaultDateFilters() { const now = new Date(); const first = new Date(now.getFullYear(), now.getMonth(), 1); const today = new Date(); const fmt = d => d.toISOString().split('T')[0]; document.getElementById('dateFromFilter').value = fmt(first); document.getElementById('dateToFilter').value = fmt(today); }

    function pasteCsvPrompt() { const s = prompt('Paste phone numbers separated by commas or new lines:', '700123456, 701234567'); if (!s) return; const arr = s.split(/[\n,]+/).map(x => x.trim()).filter(Boolean); processBulkNumbers(arr); }
    function toggleSendType() { const v = document.querySelector('input[name="sendType"]:checked').value; document.getElementById('single-recipient').classList.toggle('hidden', v !== 'single'); document.getElementById('bulk-recipients').classList.toggle('hidden', v !== 'bulk'); if (v === 'single') { bulkRecipients = []; renderRecipientTags(); } updateSendFormCalculations(); }
    function toggleSchedule() { const v = document.querySelector('input[name="sendOption"]:checked').value; document.getElementById('schedule-options').style.display = (v === 'schedule') ? 'grid' : 'none'; document.getElementById('send-button-text').textContent = (v === 'schedule') ? 'Schedule SMS' : 'Send SMS'; }
    function updateCharCount() { const msg = document.getElementById('message').value; const parts = Math.max(1, Math.ceil(msg.length / 160)); document.getElementById('char-count').textContent = `${msg.length}/160 • ${parts} part(s)`; updateSendFormCalculations(); }
    function validatePhoneNumber(n) { const x = n.replace(/\s+/g, ''); return /^0[7]\d{8}$/.test(x) || /^[7]\d{8}$/.test(x); }
    function normalizePhoneNumber(n) { const x = n.replace(/\s+/g, ''); return /^[7]\d{8}$/.test(x) ? ('0' + x) : x; }
    function addBulkRecipient() { const input = document.getElementById('bulk-number-input'); const num = input.value.trim(); if (!num) return; if (!validatePhoneNumber(num)) { showMessageModal('Invalid Number', 'Enter a valid 10-digit phone number', 'error'); return; } const norm = normalizePhoneNumber(num); if (bulkRecipients.includes(norm)) { showMessageModal('Duplicate', 'This number is already added', 'warning'); return; } bulkRecipients.push(norm); input.value = ''; renderRecipientTags(); updateSendFormCalculations(); }
    function removeRecipient(n) { bulkRecipients = bulkRecipients.filter(x => x !== n); renderRecipientTags(); updateSendFormCalculations(); }
    function renderRecipientTags() { document.getElementById('recipient-tags').innerHTML = bulkRecipients.map(n => `<span class="chip">${n}</span>`).join(''); }

    function updateSendFormCalculations() {
        const parts = Math.max(1, Math.ceil(document.getElementById('message').value.length / 160));
        const type = document.querySelector('input[name="sendType"]:checked').value;
        const rc = type === 'single' ? (document.getElementById('recipient').value.trim() ? 1 : 0) : bulkRecipients.length;
        const credits = rc * parts; const cost = credits * currentSmsRate;
        document.getElementById('recipient-count').textContent = rc;
        document.getElementById('credits-needed').textContent = credits;
        document.getElementById('estimated-cost').textContent = `Sh. ${formatCurrency(cost)}`;
    }

    function handleSendSms(e) {
        e.preventDefault();
        const message = document.getElementById('message').value.trim();
        const sendType = document.querySelector('input[name="sendType"]:checked').value;
        const sendOption = document.querySelector('input[name="sendOption"]:checked').value;
        if (!message) { showMessageModal('Missing Message', 'Please enter a message', 'error'); return; }
        let recipients = [];
        if (sendType === 'single') {
            const r = document.getElementById('recipient').value.trim();
            if (!r) { showMessageModal('Missing Recipient', 'Please enter a recipient phone number', 'error'); return; }
            if (!validatePhoneNumber(r)) { showMessageModal('Invalid Number', 'Please enter a valid 10-digit phone number', 'error'); return; }
            recipients = [normalizePhoneNumber(r)];
        } else {
            if (!bulkRecipients.length) { showMessageModal('No Recipients', 'Add at least one recipient', 'error'); return; }
            recipients = [...bulkRecipients];
        }
        let scheduledAt = null;
        if (sendOption === 'schedule') {
            const sd = document.getElementById('schedule-date').value;
            const st = document.getElementById('schedule-time').value;
            if (!sd || !st) { showMessageModal('Missing Schedule', 'Select both date and time', 'error'); return; }
            scheduledAt = `${sd} ${st}:00`;
        }
        showConfirmationModal(sendType, message, recipients, sendOption, scheduledAt);
    }

    function showConfirmationModal(sendType, message, recipients, sendOption, scheduledAt) {
        const parts = Math.max(1, Math.ceil(message.length / 160));
        const credits = recipients.length * parts;
        const total = credits * currentSmsRate;
        const scheduleText = sendOption === 'schedule' ? `<div><b>Scheduled:</b> ${formatDateTime(scheduledAt)}</div>` : `<div><b>Send:</b> Immediately</div>`;
        document.getElementById('confirmation-details').innerHTML = `<div><b>Type:</b> ${sendType === 'single' ? 'Single' : 'Bulk'}</div><div><b>Recipients:</b> ${recipients.length} (${recipients.slice(0, 3).join(', ')}${recipients.length > 3 ? '...' : ''})</div><div class="ellipsis"><b>Message:</b> ${escapeHtml(message)}</div><div><b>SMS Parts:</b> ${parts}</div><div><b>Credits Needed:</b> ${credits}</div><div><b>Total Cost:</b> Sh. ${formatCurrency(total)}</div>${scheduleText}`;
        window.pendingSmsData = { message, recipients: JSON.stringify(recipients), send_type: sendType, send_option: sendOption, scheduled_at: scheduledAt };
        document.getElementById('confirmationModal').classList.remove('hidden');
    }

    function confirmSendSms() {
        hideConfirmationModal();
        const btn = document.getElementById('send-sms-btn'); const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Sending...'; btn.disabled = true;
        api('sendSms', window.pendingSmsData, 'POST').then(resp => {
            if (resp?.success) {
                showMessageModal('Success', resp.message || 'SMS queued', 'success');
                document.getElementById('sms-form').reset();
                bulkRecipients = []; renderRecipientTags(); updateSendFormCalculations(); updateCharCount();
                loadAll();
            } else {
                showMessageModal('Error', resp?.message || 'Failed to send SMS', 'error');
            }
        }).finally(() => { btn.innerHTML = orig; btn.disabled = false; delete window.pendingSmsData; });
    }

    function hideConfirmationModal() { document.getElementById('confirmationModal').classList.add('hidden'); }
    function formatDateTime(dt) { if (!dt) return ''; const d = new Date(dt); return d.toLocaleString([], { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }); }

    function loadSmsHistory() {
        const params = {
            page: 1,
            limit: 50,
            search: document.getElementById('searchHistory').value || '',
            status: document.getElementById('statusFilter').value || '',
            date_from: document.getElementById('dateFromFilter').value || '',
            date_to: document.getElementById('dateToFilter').value || ''
        };
        api('getSmsHistory', params, 'GET').then(resp => {
            if (resp?.success) {
                smsHistory = resp.data.history || [];
                renderSmsHistory();
            } else {
                showMessageModal('Error', resp?.message || 'Failed to load history', 'error');
            }
        });
    }

    function renderSmsHistory() {
        const q = (document.getElementById('searchHistory').value || '').toLowerCase();
        const st = document.getElementById('statusFilter').value;
        const df = new Date(document.getElementById('dateFromFilter').value);
        const dt = new Date(document.getElementById('dateToFilter').value); dt.setHours(23, 59, 59, 999);
        const rows = (smsHistory || []).filter(s => {
            const txt = (s.message || '').toLowerCase();
            const okQ = !q || txt.includes(q);
            const okS = !st || s.status === st;
            const when = new Date(s.sent_at || s.scheduled_at || s.created_at || Date.now());
            const okD = (!isNaN(df) && !isNaN(dt)) ? (when >= df && when <= dt) : true;
            return okQ && okS && okD;
        });
        const body = document.getElementById('history-table-body');
        const mobile = document.getElementById('history-mobile');
        const empty = document.getElementById('history-empty-state');
        if (!rows.length) { body.innerHTML = ''; mobile.innerHTML = ''; empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');
        body.innerHTML = rows.map(s => {
            const b = badgeFor(s.status); const t = s.sent_at || s.scheduled_at || s.created_at;
            return `<tr class="row-hover" onclick="viewSmsDetails('${s.id}')">
            <td><div class="ellipsis">${escapeHtml(s.message)}</div></td>
            <td>${s.recipient_count} ${s.recipient_count > 1 ? 'recipients' : 'recipient'}<div style="color:var(--ui-muted);font-size:.75rem">${s.type}</div></td>
            <td><span class="badge ${b.c}"><i class="${b.i}"></i>${s.status}</span></td>
            <td>Sh. ${formatCurrency(s.total_cost)}<div style="color:var(--ui-muted);font-size:.75rem">${s.credits_used} credits</div></td>
            <td>${formatDateTime(t)}</td>
        </tr>`;
        }).join('');
        mobile.innerHTML = rows.map(s => {
            const b = badgeFor(s.status); const t = s.sent_at || s.scheduled_at || s.created_at;
            return `<div class="ui-card p-4" onclick="viewSmsDetails('${s.id}')">
            <div class="flex items-start justify-between"><div class="line-2" style="color:var(--ui-text)">${escapeHtml(s.message)}</div><span class="badge ${b.c}"><i class="${b.i}"></i>${s.status}</span></div>
            <div class="flex items-center justify-between mt-2" style="color:var(--ui-muted);font-size:.85rem"><span>${s.recipient_count} ${s.recipient_count > 1 ? 'recipients' : 'recipient'} • ${s.type}</span><span>${formatDateTime(t)}</span></div>
            <div class="mt-1" style="color:var(--ui-text);font-size:.9rem">Sh. ${formatCurrency(s.total_cost)} <span style="color:var(--ui-muted)">• ${s.credits_used} credits</span></div>
        </div>`;
        }).join('');
    }

    function filterHistory() { loadSmsHistory(); }
    function badgeFor(s) { return { sent: { c: 'b-sent', i: 'fas fa-check' }, scheduled: { c: 'b-scheduled', i: 'fas fa-clock' }, failed: { c: 'b-failed', i: 'fas fa-times' }, cancelled: { c: 'b-cancelled', i: 'fas fa-ban' } }[s] || { c: 'b-cancelled', i: 'fas fa-question' }; }
    function viewSmsDetails(id) {
        const s = (smsHistory || []).find(x => x.id === id); if (!s) return;
        const rec = Array.isArray(s.recipients) ? s.recipients : (s.recipients ? JSON.parse(s.recipients) : []);
        showMessageModal('SMS Details', `<div class="grid gap-2 text-sm">
        <div><b>Message:</b> ${escapeHtml(s.message)}</div>
        <div><b>Recipients:</b> ${rec.length ? rec.join(', ') : s.recipient_count}</div>
        <div><b>Status:</b> ${s.status}</div>
        <div><b>Cost:</b> Sh. ${formatCurrency(s.total_cost)}</div>
        <div><b>Credits:</b> ${s.credits_used}</div>
        <div><b>Date:</b> ${formatDateTime(s.sent_at || s.scheduled_at || s.created_at)}</div>
    </div>`, 'info');
    }

    function loadSmsTemplates() {
        api('getSmsTemplates', {}, 'POST').then(resp => {
            if (resp?.success) { smsTemplates = resp.data || []; renderSmsTemplates(); }
            else { showMessageModal('Error', resp?.message || 'Failed to load templates', 'error'); }
        });
    }

    function renderSmsTemplates() {
        const grid = document.getElementById('templates-grid');
        const empty = document.getElementById('templates-empty-state');
        const q = (document.getElementById('searchTemplates').value || '').toLowerCase();
        const list = (smsTemplates || []).filter(t => !q || t.name.toLowerCase().includes(q) || t.message.toLowerCase().includes(q));
        if (!list.length) { grid.innerHTML = ''; empty.classList.remove('hidden'); return; }
        empty.classList.add('hidden');
        grid.innerHTML = list.map(t => `
        <div class="ui-card p-5 grid gap-3">
            <div class="flex items-start justify-between">
                <div class="font-semibold ellipsis" style="color:var(--ui-text)">${escapeHtml(t.name)}</div>
                <div class="flex gap-2">
                    <button class="btn btn-light" onclick="editTemplate('${t.id}')"><i class="fas fa-pen"></i></button>
                    <button class="btn btn-danger" onclick="confirmDeleteTemplate('${t.id}')"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div class="line-3" style="color:var(--ui-muted)">${escapeHtml(t.message)}</div>
            <div class="flex items-center justify-between">
                <span class="text-xs" style="color:var(--ui-muted)">${t.message.length} chars</span>
                <button class="btn btn-ghost" onclick="useTemplate('${t.id}')">Use</button>
            </div>
        </div>`).join('');
    }

    function filterTemplates() { renderSmsTemplates(); }
    function showCreateTemplateForm() { document.getElementById('template-modal-title').textContent = 'Create Template'; document.getElementById('template-id').value = ''; document.getElementById('template-name').value = ''; document.getElementById('template-message').value = ''; updateTemplateCharCount(); document.getElementById('templateModal').classList.remove('hidden'); }
    function editTemplate(id) { const t = smsTemplates.find(x => x.id === id); if (!t) return; document.getElementById('template-modal-title').textContent = 'Edit Template'; document.getElementById('template-id').value = t.id; document.getElementById('template-name').value = t.name; document.getElementById('template-message').value = t.message; updateTemplateCharCount(); document.getElementById('templateModal').classList.remove('hidden'); }
    function confirmDeleteTemplate(id) { if (!confirm('Delete this template?')) return; deleteTemplate(id); }
    function deleteTemplate(id) {
        api('deleteTemplate', { template_id: id }, 'POST').then(resp => {
            if (resp?.success) { showMessageModal('Deleted', 'Template removed', 'success'); loadSmsTemplates(); }
            else { showMessageModal('Error', resp?.message || 'Failed to delete template', 'error'); }
        });
    }
    function useTemplate(id) {
        const t = smsTemplates.find(x => x.id === id); if (!t) return;
        document.getElementById('message').value = t.message; updateCharCount(); switchTab('send');
        showMessageModal('Template Applied', `Template "${escapeHtml(t.name)}" inserted`, 'success');
    }
    function handleSaveTemplate(e) {
        e.preventDefault();
        const id = document.getElementById('template-id').value || '';
        const name = document.getElementById('template-name').value.trim();
        const msg = document.getElementById('template-message').value.trim();
        if (!name || !msg) { showMessageModal('Missing Info', 'Fill all fields', 'error'); return; }
        api('saveTemplate', { template_id: id, name: name, message: msg }, 'POST').then(resp => {
            if (resp?.success) { hideTemplateModal(); loadSmsTemplates(); showMessageModal('Saved', resp.message || 'Template saved', 'success'); }
            else { showMessageModal('Error', resp?.message || 'Failed to save template', 'error'); }
        });
    }

    function updateTemplateCharCount() { document.getElementById('template-char-count').textContent = `${document.getElementById('template-message').value.length}/160`; }
    function hideTemplateSelector() { document.getElementById('templateSelectorModal').classList.add('hidden'); }
    function selectTemplate(id) { useTemplate(id); hideTemplateSelector(); }
    function showTemplateSelector() {
        if (!smsTemplates.length) { loadSmsTemplates(); }
        document.getElementById('template-selector-list').innerHTML = (smsTemplates?.length)
            ? smsTemplates.map(t => `<div class="ui-card p-3 row-hover" onclick="selectTemplate('${t.id}')"><div class="font-medium ellipsis" style="color:var(--ui-text)">${escapeHtml(t.name)}</div><div class="text-sm line-2" style="color:var(--ui-muted)">${escapeHtml(t.message)}</div></div>`).join('')
            : '<div class="py-8 text-center" style="color:var(--ui-muted)">No templates</div>';
        document.getElementById('templateSelectorModal').classList.remove('hidden');
    }
    function hideTemplateModal() { document.getElementById('templateModal').classList.add('hidden'); }

    function updatePackageCosts() {
        document.getElementById('package-100-cost').textContent = `Sh. ${formatCurrency(100 * currentSmsRate)}`;
        document.getElementById('package-500-cost').textContent = `Sh. ${formatCurrency(500 * currentSmsRate)}`;
        document.getElementById('package-1000-cost').textContent = `Sh. ${formatCurrency(1000 * currentSmsRate)}`;
    }
    function updateTopupCalculation() {
        const r = document.querySelector('input[name="package"]:checked');
        const customSec = document.getElementById('custom-amount-section'); let credits = 0, cost = 0;
        if (!r) { document.querySelector('[data-pkg="100"]').click(); return; }
        if (r.value === 'custom') { customSec.classList.remove('hidden'); const amt = parseFloat(document.getElementById('custom-amount').value) || 0; cost = amt; credits = Math.floor(cost / currentSmsRate); }
        else { customSec.classList.add('hidden'); credits = parseInt(r.value); cost = credits * currentSmsRate; }
        document.getElementById('topup-credits').textContent = credits;
        document.getElementById('topup-cost').textContent = `Sh. ${formatCurrency(cost)}`;
        const balEl = document.getElementById('balance-status'); const warn = document.getElementById('insufficient-balance-warning'); const btn = document.getElementById('purchase-credits-btn');
        if (cost > walletBalance) { balEl.textContent = `Sh. ${formatCurrency(walletBalance)} (Insufficient)`; balEl.style.color = 'var(--ui-danger)'; warn.classList.remove('hidden'); btn.disabled = true; }
        else { balEl.textContent = `Sh. ${formatCurrency(walletBalance)} (Sufficient)`; balEl.style.color = 'var(--ui-success)'; warn.classList.add('hidden'); btn.disabled = false; }
    }

    function handlePurchaseCredits(e) {
        e.preventDefault();
        const r = document.querySelector('input[name="package"]:checked'); if (!r) { showMessageModal('No Package', 'Select a package', 'error'); return; }
        let amount = 0;
        if (r.value === 'custom') { amount = parseFloat(document.getElementById('custom-amount').value) || 0; if (amount < 1000) { showMessageModal('Invalid Amount', 'Minimum Sh. 1,000', 'error'); return; } }
        else { amount = parseInt(r.value) * currentSmsRate; }
        if (amount > walletBalance) { showMessageModal('Insufficient Balance', 'Your wallet balance is insufficient', 'error'); return; }
        const btn = document.getElementById('purchase-credits-btn'); const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Processing...'; btn.disabled = true;
        api('purchaseSmsCredits', { amount: amount }, 'POST').then(resp => {
            if (resp?.success) {
                showMessageModal('Success', resp.message || 'Credits purchased', 'success');
                loadAll();
                document.getElementById('topup-form').reset();
                document.querySelectorAll('.pkg').forEach(x => x.classList.remove('sel'));
            } else {
                showMessageModal('Error', resp?.message || 'Failed to purchase credits', 'error');
            }
        }).finally(() => { btn.innerHTML = orig; btn.disabled = false; });
    }

    function downloadSampleTemplate() {
        const csv = "Phone Number\n700123456\n701234567\n702345678";
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = 'sms_bulk_template.csv'; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
        showMessageModal('Template Downloaded', 'Sample CSV downloaded', 'success');
    }

    function handleBulkUpload(e) {
        const f = e.target.files[0]; if (!f) return;
        const ext = f.name.split('.').pop().toLowerCase(); if (ext !== 'csv') { showMessageModal('Invalid File', 'Upload a CSV file', 'error'); return; }
        const reader = new FileReader();
        reader.onload = ev => { const lines = (ev.target.result || '').split('\n'); const nums = []; for (let i = 1; i < lines.length; i++) { const line = lines[i].trim(); if (!line) continue; const num = line.split(',')[0].trim().replace(/['"]/g, ''); if (num) nums.push(num); } processBulkNumbers(nums); };
        reader.readAsText(f); e.target.value = '';
    }

    function processBulkNumbers(arr) {
        const results = { valid: [], invalid: [], duplicates: [], total: arr.length };
        arr.forEach(n => {
            const clean = n.replace(/\s+/g, ''); const norm = normalizePhoneNumber(clean);
            if (bulkRecipients.includes(norm) || results.valid.includes(norm)) results.duplicates.push(norm);
            else if (validatePhoneNumber(clean)) results.valid.push(norm);
            else results.invalid.push(clean);
        });
        bulkRecipients.push(...results.valid); renderRecipientTags(); updateSendFormCalculations(); showBulkUploadResults(results);
    }
    function showBulkUploadResults(r) {
        const content = document.getElementById('upload-results-content');
        content.innerHTML = `<div class="grid gap-3">
        <div class="ui-card p-3" style="background:rgba(22,163,74,.06);border-color:rgba(22,163,74,.3)">
            <div class="font-semibold" style="color:#16a34a">Added: ${r.valid.length}</div>
        </div>
        ${r.invalid.length ? `<div class="ui-card p-3" style="background:rgba(220,38,38,.06);border-color:rgba(220,38,38,.3)"><div class="font-semibold" style="color:#dc2626">Invalid: ${r.invalid.length}</div><div class="text-sm" style="color:#b91c1c;max-height:120px;overflow:auto">${r.invalid.slice(0, 10).map(x => `<div>${x}</div>`).join('')}${r.invalid.length > 10 ? `<div>...and ${r.invalid.length - 10} more</div>` : ''}</div>` : ''}
        ${r.duplicates.length ? `<div class="ui-card p-3" style="background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.3)"><div class="font-semibold" style="color:#b45309">Duplicates: ${r.duplicates.length}</div></div>` : ''}
        <div class="ui-card p-3"><div class="grid grid-cols-2 gap-2 text-sm"><div>Total: ${r.total}</div><div>Added: ${r.valid.length}</div><div>Invalid: ${r.invalid.length}</div><div>Duplicates: ${r.duplicates.length}</div></div></div>
    </div>`;
        document.getElementById('bulkUploadModal').classList.remove('hidden');
    }
    function hideBulkUploadModal() { document.getElementById('bulkUploadModal').classList.add('hidden'); }

    function showMessageModal(title, html, type = 'info') {
        const colors = { success: ['rgba(22,163,74,.2)', '#16a34a', 'fas fa-check'], error: ['rgba(220,38,38,.2)', '#dc2626', 'fas fa-times'], warning: ['rgba(245,158,11,.2)', '#b45309', 'fas fa-exclamation-triangle'], info: ['rgba(37,99,235,.2)', '#2563eb', 'fas fa-info'] };
        const [bg, fg, icon] = colors[type] || colors.info;
        document.getElementById('message-modal-title').textContent = title;
        const iconEl = document.getElementById('message-modal-icon'); iconEl.style.background = bg; iconEl.style.color = fg; iconEl.innerHTML = `<i class="${icon}"></i>`;
        document.getElementById('message-modal-text').innerHTML = html;
        document.getElementById('messageModal').classList.remove('hidden');
    }
    function hideMessageModal() { document.getElementById('messageModal').classList.add('hidden'); }

    function insertAtCursor(el, txt) { const start = el.selectionStart || 0; const end = el.selectionEnd || 0; const val = el.value; el.value = val.substring(0, start) + txt + val.substring(end); el.focus(); const pos = start + txt.length; el.setSelectionRange(pos, pos); el.dispatchEvent(new Event('input')); }
    function escapeHtml(s) { return (s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m])); }
    function formatCurrency(n) { return (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>