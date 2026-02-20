<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Fees Terminal | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin Theme Colors */
        :root { --admin-dark: #0f172a; --admin-blue: #2563eb; }
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .paid { background: #2563eb !important; color: white !important; border-color: #1d4ed8 !important; }
        .not-paid { background: #ffffff; color: #94a3b8; border: 1px solid #e2e8f0; }
        .day-box { font-size: 10px; font-weight: 700; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
        /* Smooth transition for mobile keyboard */
        .sticky-receipt { position: sticky; top: 1rem; }
        @media (max-width: 1024px) { .sticky-receipt { position: static; } }
    </style>
</head>
<body class="text-slate-900 font-sans leading-tight">

    <nav class="bg-slate-900 text-white p-4 shadow-lg mb-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="admin_page.php" class="hover:bg-slate-800 p-2 rounded-lg transition"><i class="fas fa-th-large"></i></a>
                <span class="font-black uppercase tracking-tighter text-lg">LMS <span class="text-blue-500">Admin</span></span>
            </div>
            <div class="text-[10px] font-bold bg-blue-600 px-3 py-1 rounded-full uppercase">Fees Manager</div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 pb-10">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="flex items-center gap-4">
                <button onclick="navigate(-1)" class="bg-white p-4 rounded-2xl shadow-sm hover:bg-blue-50 transition active:scale-90 text-blue-600 border border-slate-200"><i class="fas fa-chevron-left"></i></button>
                <div class="flex-1 bg-white p-3 rounded-2xl shadow-sm border border-slate-200 text-center">
                    <p id="nav_label_main" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Year View</p>
                    <h2 id="nav_current_val" class="text-xl font-black text-slate-800 uppercase">2026</h2>
                </div>
                <button onclick="navigate(1)" class="bg-white p-4 rounded-2xl shadow-sm hover:bg-blue-50 transition active:scale-90 text-blue-600 border border-slate-200"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="flex p-1 bg-slate-200 rounded-2xl h-full">
                <button onclick="setView('yearly')" id="tab-year" class="flex-1 py-3 rounded-xl text-xs font-black uppercase transition-all bg-white shadow-sm text-blue-600">Yearly</button>
                <button onclick="setView('monthly')" id="tab-month" class="flex-1 py-3 rounded-xl text-xs font-black uppercase transition-all text-slate-500">Monthly</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Find Student</label>
                    <div class="flex gap-2">
                        <input type="text" id="student_search" placeholder="Enter SID..." class="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-3 font-bold focus:ring-2 focus:ring-blue-500 outline-none border">
                        <button onclick="searchStudent()" class="bg-slate-900 text-white px-5 rounded-xl hover:bg-blue-600 transition active:scale-95"><i class="fas fa-search"></i></button>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-lg border-t-4 border-blue-600 sticky-receipt">
                    <div class="border-b border-dashed border-slate-200 pb-4 mb-4 text-center">
                        <h3 class="font-black text-slate-800 uppercase italic text-lg">Invoice Summary</h3>
                        <p id="bill_period" class="text-[10px] font-bold text-slate-400 uppercase">Year 2026</p>
                    </div>

                    <div id="bill_student" class="hidden mb-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p id="bill_name" class="font-black text-slate-800 uppercase text-sm">---</p>
                        <p id="bill_id" class="text-[9px] font-bold text-blue-600 uppercase tracking-widest mt-0.5">---</p>
                    </div>

                    <div id="bill_items" class="space-y-2 mb-6 max-h-[300px] overflow-y-auto pr-1">
                        <div class="text-center py-6 opacity-20"><i class="fas fa-receipt text-4xl"></i></div>
                    </div>

                    <div id="dynamic_selectors" class="hidden space-y-3 mb-6 bg-slate-50 p-4 rounded-2xl">
                        <div>
                            <label class="text-[9px] font-black text-slate-500 uppercase ml-1 block mb-1">Billing Month</label>
                            <select id="bill_month_select" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-bold text-sm outline-none">
                                <?php 
                                $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                                foreach($months as $m) echo "<option value='$m' ".(date('F')==$m?'selected':'').">$m</option>";
                                ?>
                            </select>
                        </div>
                        <div id="date_selector_container" class="hidden">
                            <label class="text-[9px] font-black text-slate-500 uppercase ml-1 block mb-1">Billing Date</label>
                            <select id="bill_day_select" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-bold text-sm outline-none">
                                <?php for($d=1; $d<=31; $d++) echo "<option value='$d' ".((int)date('d')==$d?'selected':'').">$d</option>"; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-6 px-2">
                        <span class="text-xs font-bold text-slate-400 uppercase">Grand Total</span>
                        <span class="text-2xl font-black text-slate-900" id="bill_total">RS. 0</span>
                    </div>

                    <button onclick="processPayment()" id="pay_btn" class="hidden w-full bg-blue-600 text-white py-4 rounded-xl font-black uppercase tracking-widest shadow-lg shadow-blue-100 hover:bg-blue-700 transition active:scale-95">
                        Confirm & Pay
                    </button>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div id="class_grid" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                    </div>

                <div id="placeholder" class="bg-white p-20 rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                    <i class="fas fa-user-circle text-6xl text-slate-200 mb-4"></i>
                    <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">Search for a student to view records</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let viewMode = 'yearly';
        let currentYear = new Date().getFullYear();
        let currentMonthIdx = new Date().getMonth();
        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        let selectedClasses = [];
        let currentStudent = null;

        function setView(v) {
            viewMode = v;
            document.getElementById('tab-year').className = (v === 'yearly') ? 'flex-1 py-3 rounded-xl text-xs font-black uppercase transition-all bg-white shadow-sm text-blue-600' : 'flex-1 py-3 rounded-xl text-xs font-black uppercase transition-all text-slate-500';
            document.getElementById('tab-month').className = (v === 'monthly') ? 'flex-1 py-3 rounded-xl text-xs font-black uppercase transition-all bg-white shadow-sm text-blue-600' : 'flex-1 py-3 rounded-xl text-xs font-black uppercase transition-all text-slate-500';
            updateNavLabel();
            if(currentStudent) searchStudent();
        }

        function updateNavLabel() {
            if(viewMode === 'yearly') {
                document.getElementById('nav_label_main').innerText = "Year View";
                document.getElementById('nav_current_val').innerText = currentYear;
                document.getElementById('bill_period').innerText = "Year " + currentYear;
            } else {
                document.getElementById('nav_label_main').innerText = "Month View (" + currentYear + ")";
                document.getElementById('nav_current_val').innerText = months[currentMonthIdx];
                document.getElementById('bill_period').innerText = months[currentMonthIdx] + " " + currentYear;
            }
        }

        function navigate(dir) {
            if(viewMode === 'yearly') {
                currentYear += dir;
            } else {
                currentMonthIdx += dir;
                if(currentMonthIdx > 11) { currentMonthIdx = 0; currentYear++; }
                if(currentMonthIdx < 0) { currentMonthIdx = 11; currentYear--; }
            }
            updateNavLabel();
            if(currentStudent) searchStudent();
        }

        function searchStudent() {
            const id = document.getElementById('student_search').value.trim();
            if(!id) return;
            fetch(`fetch_student_fees_ajax.php?id=${id}&month=${months[currentMonthIdx]}&year=${currentYear}`)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    currentStudent = data.student;
                    renderClasses(data.classes);
                    document.getElementById('class_grid').classList.remove('hidden');
                    document.getElementById('placeholder').classList.add('hidden');
                    document.getElementById('bill_student').classList.remove('hidden');
                    document.getElementById('pay_btn').classList.remove('hidden');
                    document.getElementById('bill_name').innerText = data.student.name;
                    document.getElementById('bill_id').innerText = "STUDENT ID: " + data.student.id;
                } else { alert(data.message); }
            });
        }

        function renderClasses(classes) {
            const grid = document.getElementById('class_grid');
            let html = '';
            classes.forEach(cls => {
                let calendar = '';
                if(viewMode === 'yearly') {
                    calendar = `<div class="grid grid-cols-4 gap-1.5 mt-3">
                        ${months.map(m => `<div class="day-box uppercase ${cls.paid_months.includes(m) ? 'paid' : 'not-paid'}">${m.substring(0,3)}</div>`).join('')}
                    </div>`;
                } else {
                    calendar = `<div class="grid grid-cols-7 gap-1 mt-3">
                        ${Array.from({length: 31}, (_, i) => i + 1).map(d => `<div class="day-box ${cls.paid_days.includes(d) ? 'paid' : 'not-paid'}">${d}</div>`).join('')}
                    </div>`;
                }
                html += `
                    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-black text-slate-800 uppercase text-xs leading-none">${cls.subject}</h4>
                                <span class="text-[8px] font-bold text-blue-500 uppercase tracking-widest">${cls.fee_type}</span>
                            </div>
                            <input type="checkbox" class="w-6 h-6 accent-blue-600 rounded cursor-pointer" onchange="toggleItem('${cls.class_id}', '${cls.subject}', ${cls.class_fee}, '${cls.fee_type}')">
                        </div>
                        <p class="text-slate-900 font-black text-sm mb-2">RS. ${parseFloat(cls.class_fee).toFixed(0)}</p>
                        ${calendar}
                    </div>`;
            });
            grid.innerHTML = html;
        }

        function toggleItem(id, name, fee, type) {
            const idx = selectedClasses.findIndex(x => x.id === id);
            if(idx > -1) selectedClasses.splice(idx, 1);
            else selectedClasses.push({id, name, fee: parseFloat(fee), type: type});
            updateBill();
        }

        function updateBill() {
            const container = document.getElementById('bill_items');
            const totalEl = document.getElementById('bill_total');
            const selectors = document.getElementById('dynamic_selectors');
            const dateContainer = document.getElementById('date_selector_container');

            if(selectedClasses.length === 0) {
                container.innerHTML = '<div class="text-center py-6 opacity-20"><i class="fas fa-receipt text-4xl"></i></div>';
                totalEl.innerText = 'RS. 0';
                selectors.classList.add('hidden');
                return;
            }

            selectors.classList.remove('hidden');
            const hasDayType = selectedClasses.some(item => item.type === 'Day');
            dateContainer.classList.toggle('hidden', !hasDayType);

            let html = '', total = 0;
            selectedClasses.forEach(item => {
                html += `<div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div>
                        <p class="font-bold text-[10px] text-slate-700 uppercase">${item.name}</p>
                        <p class="text-[8px] font-bold text-slate-400 uppercase">${item.type}</p>
                    </div>
                    <p class="font-black text-slate-900 text-xs">RS. ${item.fee.toFixed(0)}</p>
                </div>`;
                total += item.fee;
            });
            container.innerHTML = html;
            totalEl.innerText = `RS. ${total.toFixed(0)}`;
        }

        function processPayment() {
            if(selectedClasses.length === 0) return alert('Please select a class!');
            const btn = document.getElementById('pay_btn');
            const payload = {
                student_id: currentStudent.id,
                month: document.getElementById('bill_month_select').value,
                day: document.getElementById('bill_day_select').value,
                year: currentYear,
                classes: selectedClasses
            };

            btn.disabled = true; btn.innerText = "Processing...";
            fetch('process_fees_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') { alert('Payment Recorded Successfully!'); searchStudent(); selectedClasses = []; updateBill(); }
                else { alert(data.message); }
                btn.disabled = false; btn.innerText = "Confirm & Pay";
            });
        }
    </script>
</body>
</html>