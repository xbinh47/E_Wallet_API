$(function () {

    let gridColor = '#5b5b5b'

    const pie1_legend = ['Income', 'Spending']
    const pie1_bg = ['#4b961c', '#ff084a']
    const pie1_title = 'Today Income/Spending Scale'

    const pie2_legend = ['Deposit', 'Withdraw', 'Transfer', 'Top-up Card']
    const pie2_bg = ['#4b961c', '#ff084a', '#03396c', '#ffbf00']
    const pie2_title = 'Today Transaction Scale'

    const line1_legend = ['', '', '', '', '', '', '']
    const line1_bg = pie2_bg
    const line1_title = 'Transition Scale in recent days'

    const pie3_legend = pie1_legend
    const pie3_bg = pie1_bg
    const pie3_title = 'Income/Spending Scale in recent days'

    // tooltip

    $('[data-bs-toggle="tooltip"]').tooltip()
    $(window).resize(function () {
        if($(window).width() < 820){
            $('[data-bs-toggle="tooltip"]').tooltip('disable')
        }else{
            $('[data-bs-toggle="tooltip"]').tooltip('enable')
        }
    })
    // datetime

    const today = new Date()
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    let week = []

    Date.prototype.toDateInputValue = (function () {
        var local = new Date(this)
        local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
        return local.toJSON().slice(0, 10);
    })

    for (let i = 0; i < 7; i++) {
        let day = new Date(Date.now() - i * 24 * 60 * 60 * 1000)
        day = day.toString().split(' ')
        week.unshift(day[3] + '-' + ((months.indexOf(day[1]) + 1) < 10 ? '0' + String(months.indexOf(day[1]) + 1) : months.indexOf(day[1]) + 1) + '-' + day[2])
    }

    $('#deposit-expiration-date').val(today.toDateInputValue())
    $('#withdraw-expiration-date').val(today.toDateInputValue())

    function dateTimeHandle() {
        const today = new Date()
        let temp = today.toDateString()
        temp = temp.split(' ')
        const date = temp[0] + ', ' + temp[1] + ' ' + temp[2] + ', ' + temp[3]
        const time = today.getHours() + ":" + (today.getMinutes() < 10 ? '0' : '') + today.getMinutes() + ":" + (today.getSeconds() < 10 ? '0' : '') + today.getSeconds()
        $('#date').text(date)
        $('#time').text(time)
    }
    dateTimeHandle()
    setInterval(dateTimeHandle, 1000)

    function limitBirthday() {
        const today = new Date();
        let month = today.getMonth() + 1;
        let day = today.getDate();
        const year = today.getFullYear();

        if (month < 10)
            month = '0' + month.toString();
        if (day < 10)
            day = '0' + day.toString();

        const maxDate = year + '-' + month + '-' + day;
        $('#birthday-input').attr('max', maxDate);
    }

    limitBirthday()

    // sidebar handle

    let currShow = 'home'

    if ($('#permission').text() == 'admin') {
        currShow = 'account-management'
        $('#section-name').text('Account Management')
    }

    function showSection(btn, section) {
        $('#' + section.replace(' ', '-').toLowerCase()).fadeIn()
        $('#section-name').text(section)
        $('#section-expand').text('')
        $('#' + currShow + '-btn > i').removeClass('icon-active')
        if (section == 'History' || section == 'Dashboard' || section == 'Your Account') $('#home-btn>i').removeClass('icon-active')
        btn.children().addClass('icon-active')
        currShow = section.replace(' ', '-').toLowerCase()
    }

    $('#home-btn').click(function () {
        $('#' + currShow).fadeOut(200, function () {
            showSection($('#home-btn'), 'Home')
        })
    })

    $('#history-btn').click(function () {
        $('#' + currShow).fadeOut(200, function () {
            showSection($('#history-btn'), 'History')
        })
    })

    $('#dashboard-btn').click(function () {
        $('#' + currShow).fadeOut(200, function () {
            showSection($('#dashboard-btn'), 'Dashboard')
        })
    })

    $('#your-account-btn').click(function () {
        $('#' + currShow).fadeOut(200, function () {
            showSection($('#your-account-btn'), 'Your Account')
        })
    })

    $('#account-management-btn').click(function () {
        $('#' + currShow).fadeOut(200, function () {
            showSection($('#account-management-btn'), 'Account Management')
        })
    })

    $('#confirm-transaction-btn').click(function () {
        $('#' + currShow).fadeOut(200, function () {
            showSection($('#confirm-transaction-btn'), 'Confirm Transaction')
        })
    })

    // balance handle

    function numberWithDot(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // history and lasted transaction handle 

    let histories = []

    function slitDate(str) {
        const result = str.split(' ')
        return result[0]
    }


    let weekDeposit = [0, 0, 0, 0, 0, 0, 0]
    let weekWithdraw = [0, 0, 0, 0, 0, 0, 0]
    let weekTransfer = [0, 0, 0, 0, 0, 0, 0]
    let weekTransfer1 = [0, 0, 0, 0, 0, 0, 0]
    let weekTransfer2 = [0, 0, 0, 0, 0, 0, 0]
    let weekCard = [0, 0, 0, 0, 0, 0, 0]
    let todayTransaction = [0, 0, 0, 0]
    let todayHis = [0, 0]
    let weekHis = [0, 0]

    function transIcon(type) {
        if (type == 'deposit') {
            return 'fa-arrow-right-to-bracket deposit-icon'
        } else if (type == 'withdraw') {
            return 'fa-dollar-sign withdraw-icon'
        } else if (type == 'transfer') {
            return 'fa-money-bill-transfer transfer-icon'
        } else {
            return 'fa-mobile-screen topUp-card-icon'
        }
    }

    function transStatus(status) {
        if (status == '1') {
            return {
                status: 'success',
                statusColor: 'var(--green-color);'
            }
        } else if (status == '0') {
            return {
                status: 'waiting',
                statusColor: 'var(--blue-input-color);'
            }
        } else {
            return {
                status: 'fail',
                statusColor: 'var(--red-color);'
            }
        }
    }

    const email = $('#permission-email').text()
    if ($('#permission').text() == 'user') {
        const chart1 = createChart(document.querySelector('#chart1').getContext('2d'), 'doughnut', pieOpt(pie1_legend, pie1_bg, todayHis, pie1_title))
        const chart2 = createChart(document.querySelector('#chart2').getContext('2d'), 'doughnut', pieOpt(pie2_legend, pie2_bg, todayTransaction, pie2_title))
        const chart3 = createChart(document.querySelector('#chart3').getContext('2d'), 'line', lineOpt(line1_legend, line1_bg, {
            weekDeposit,
            weekWithdraw,
            weekTransfer,
            weekCard
        }, line1_title))
        const chart4 = createChart(document.querySelector('#chart4').getContext('2d'), 'doughnut', pieOpt(pie3_legend, pie3_bg, weekHis, pie3_title))

        function updateHistory() {

            weekDeposit = [0, 0, 0, 0, 0, 0, 0]
            weekWithdraw = [0, 0, 0, 0, 0, 0, 0]
            weekTransfer = [0, 0, 0, 0, 0, 0, 0]
            weekTransfer1 = [0, 0, 0, 0, 0, 0, 0]
            weekTransfer2 = [0, 0, 0, 0, 0, 0, 0]
            weekCard = [0, 0, 0, 0, 0, 0, 0]
            todayTransaction = [0, 0, 0, 0]
            todayHis = [0, 0]
            weekHis = [0, 0]

            $.ajax({
                url: '../api/user/gettrans.php',
                type: 'POST',
                async: false,
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    email: email
                })
            })
                .done(function (data) {
                    if (data.code == 0) histories = data.data
                })

            $('#history-list').html('')
            $('.transaction-list').html('')

            if (histories.length != 0) {
                histories.forEach((history, index) => {
                    const icon = transIcon(history.transtype)
                    const statusColor = transStatus(history.approval).statusColor
                    const status = transStatus(history.approval).status
                    $('#history-list').append(
                        `<tr idTrans="${history.idtrans}" transtype="${history.transtype}" class="history-item">` +
                        `<td class="d-flex align-items-center">` +
                        `<i` +
                        ` class="fa-solid ${icon} transaction-icon me-3"></i>` +
                        `${history.transtype}` +
                        `</td>` +
                        `<td style="color: ${statusColor}">${status}</td>` +
                        `<td>${history.datetrans}</td>` +
                        `<td>${numberWithDot(history.amount)} đ</td>` +
                        `</tr>`
                    )

                    if (index < 6) {
                        $('.transaction-list').append(
                            `<li class="transaction-item">` +
                            `<i class="fa-solid ${icon} transaction-icon"></i>` +
                            `<div>${history.transtype}</div>` +
                            `<div>${numberWithDot(history.amount)} đ</div>` +
                            `</li>`
                        )
                    }

                    const time = slitDate(history.datetrans)
                    if (week.includes(time) && status == 'success') {
                        const amount = Number(history.amount)
                        const weekIndex = week.indexOf(time)
                        if (history.transtype == 'deposit') {
                            weekDeposit[weekIndex] += amount
                        } else if (history.transtype == 'withdraw') {
                            weekWithdraw[weekIndex] += amount
                        } else if (history.transtype == 'transfer') {
                            if ($('#permission-phone').text() == history.receiver) {
                                weekTransfer2[weekIndex] += amount
                            } else {
                                weekTransfer1[weekIndex] += amount
                            }
                        } else {
                            weekCard[weekIndex] += amount
                        }
                    }
                })
            }
            updateChart()

            $('.history-item').click(function () {
                const detailItem = $(this)
                const idtrans = detailItem.attr('idtrans')
                const transtype = detailItem.attr('transtype')
                $.ajax({
                    url: '../api/user/gettransdetail.php',
                    method: 'POST',
                    async: false,
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        idtrans: idtrans,
                        transtype: transtype
                    })
                })
                    .done(function (data) {
                        const historyDetail = data.data
                        $('#history-idtrans').text(historyDetail[0].idtrans)
                        $('#history-transtype').text(historyDetail[0].transtype)
                        $('#history-date').text(historyDetail[0].datetrans)
                        $('#history-amount').text(historyDetail[0].amount)
                        if (historyDetail[0].transtype == 'deposit') {
                            $('#card-wrap').hide()
                            $('#receiver-wrap').hide()
                            $('#note-wrap').hide()
                        } else if (historyDetail[0].transtype == 'withdraw') {
                            $('#history-note').text(historyDetail[0].note)
                            $('#receiver-wrap').hide()
                            $('#card-wrap').hide()
                            $('#note-wrap').show()
                        } else if (historyDetail[0].transtype == 'transfer') {
                            $('#history-receiver').text(historyDetail[0].receiver)
                            $('#history-note').text(historyDetail[0].note)
                            $('#card-wrap').hide()
                            $('#receiver-wrap').show()
                            $('#note-wrap').show()
                        } else {
                            $('#history-network').text(historyDetail[0].networkname)
                            $('#history-price').text(historyDetail[0].price)
                            $('#history-quantity').text(historyDetail.length)
                            $('#card-num>div').html('')
                            historyDetail.forEach(value => {
                                $('#card-num>div').append(`<p>${value.cardcode}</p>`)
                            })
                            $('#receiver-wrap').hide()
                            $('#note-wrap').hide()
                            $('#card-wrap').show()
                        }
                        $('#history-modal').modal('show')
                    })
            })

            $('.transaction-item').click(function () {
                $('#' + currShow).fadeOut(200, function () {
                    showSection($('#history-btn'), 'History')
                })
            })
        }

        updateHistory()

        // update chart

        function updateChart(phone) {
            for (let i = 0; i < 7; i++) {
                weekHis[0] += weekDeposit[i] + weekTransfer2[i]
                weekHis[1] += weekWithdraw[i] + weekTransfer1[i] + weekCard[i]
                weekTransfer[i] += weekTransfer1[i] + weekTransfer2[i]
            }
            todayHis[0] = weekDeposit[6] + weekTransfer2[6]
            todayHis[1] = weekWithdraw[6] + weekTransfer1[6] + weekCard[6]
            todayTransaction[0] = weekDeposit[6]
            todayTransaction[1] = weekWithdraw[6]
            todayTransaction[2] = weekTransfer[6]
            todayTransaction[3] = weekCard[6]
            updateChartInfo()
            chart1.data.datasets[0].data = todayHis.slice()
            chart2.data.datasets[0].data = todayTransaction.slice()
            chart3.data.datasets[0].data = weekDeposit.slice()
            chart3.data.datasets[1].data = weekWithdraw.slice()
            chart3.data.datasets[2].data = weekTransfer.slice()
            chart3.data.datasets[3].data = weekCard.slice()
            chart4.data.datasets[0].data = weekHis.slice()
            updateAllChart()
        }

        function updateChartInfo() {
            $('#week-income').text(numberWithDot(weekHis[0]) + ' đ')
            $('#week-spending').text(numberWithDot(weekHis[1]) + ' đ')
            const total = {
                deposit: 0,
                withdraw: 0,
                transfer: 0,
                card: 0
            }
            for (let i = 0; i < 7; i++) {
                total.deposit += weekDeposit[i]
                total.withdraw += weekWithdraw[i]
                total.transfer += weekTransfer[i]
                total.card += weekCard[i]
            }
            $('#week-deposit').text(numberWithDot(total.deposit) + ' đ')
            $('#week-withdraw').text(numberWithDot(total.withdraw) + ' đ')
            $('#week-transfer').text(numberWithDot(total.transfer) + ' đ')
            $('#week-card').text(numberWithDot(total.card) + ' đ')
            $('#increase-quantity').text(numberWithDot(todayHis[0]) + ' đ')
            $('#descrease-quantity').text(numberWithDot(todayHis[1]) + ' đ')
        }

        function updateAllChart() {
            chart1.update()
            chart2.update()
            chart3.update()
            chart4.update()
        }
    }

    // Chart

    function createChart(chart, type, opt) {
        return new Chart(chart, {
            type: type,
            data: opt.data,
            options: opt.opt
        })
    }

    function pieOpt(label, bgColor, quantity, title) {
        const data = {
            labels: label,
            datasets: [{
                backgroundColor: bgColor,
                borderColor: '#26292f',
                hoverOffset: 6,
                data: quantity
            }]
        }
        const opt = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            size: 14,
                        }
                    }
                },
                title: {
                    display: true,
                    text: title,
                    font: {
                        size: 18
                    }
                }
            },

        }
        return {
            data: data,
            opt: opt
        }
    }

    function lineOpt(label, bgColor, quantity, title) {
        const data = {
            labels: label,
            datasets: [{
                label: 'Deposit',
                backgroundColor: bgColor[0],
                borderColor: bgColor[0],
                data: quantity.weekDeposit
            },
            {
                label: 'Withdraw',
                backgroundColor: bgColor[1],
                borderColor: bgColor[1],
                data: quantity.weekWithdraw
            },
            {
                label: 'Transfer',
                backgroundColor: bgColor[2],
                borderColor: bgColor[2],
                data: quantity.weekTransfer
            },
            {
                label: 'Top-up Card',
                backgroundColor: bgColor[3],
                borderColor: bgColor[3],
                data: quantity.weekCard
            }
            ]
        }
        const opt = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                },
                title: {
                    display: true,
                    text: title,
                    font: {
                        size: 18
                    }
                }
            },
            scales: {
                x: {
                    display: false
                },
                y: {
                    grid: {
                        color: gridColor
                    },
                    display: true,
                    title: {
                        display: true,
                        text: 'Value'
                    },
                    suggestedMin: 0,
                    suggestedMax: 1000
                }
            }
        }
        return {
            data: data,
            opt: opt
        }
    }

    //section name handle

    function showHome() {
        $('#home').fadeIn()
        currShow = 'home'
    }

    function hideSection() {
        if ($('#section-expand').text() != '') {
            $('#' + currShow).fadeOut(200, function () {
                showHome()
            })
            $('#section-expand').text('')
        }
    }

    $('#section-name').click(function () {
        hideSection()
        resetMes()
    })

    function showFunction(name) {
        $('#section-expand').text(' > ' + name)
        $('#home').fadeOut(200, function () {
            if (name == 'Deposit') {
                $('#deposit-function').fadeIn()
                currShow = 'deposit-function'
            } else if (name == 'Withdraw') {
                $('#withdraw-function').fadeIn()
                currShow = 'withdraw-function'
            } else if (name == 'Transfer') {
                $('#transfer-function').fadeIn()
                currShow = 'transfer-function'
            } else {
                $('#card-function').fadeIn()
                currShow = 'card-function'
            }
        })
    }

    if ($('#permission').text() == 'new') {
        $('#deposit-btn').click(function () {
            showMes('This feature is only available for verified accounts', 'error')
        })
        $('#withdraw-btn').click(function () {
            showMes('This feature is only available for verified accounts', 'error')
        })
        $('#transfer-btn').click(function () {
            showMes('This feature is only available for verified accounts', 'error')
        })
        $('#card-btn').click(function () {
            showMes('This feature is only available for verified accounts', 'error')
        })
    }

    // deposit handle

    $('#deposit').click(function () {
        showFunction('Deposit')
    })

    // withdraw handle

    $('#withdraw').click(function () {
        showFunction('Withdraw')
    })

    // transfer handle

    $('#transfer').click(function () {
        showFunction('Transfer')
    })

    // deposit handle

    $('#topUp-card').click(function () {
        showFunction('Top-up Card')
    })

    // change password handle

    function resetInput() {
        $('#input-current-password').val('')
        $('#input-new-password').val('')
        $('#input-confirm-password').val()
    }

    $('#close-pass-form').click(function () {
        $('#current-pass-mes').text('')
        $('#new-pass-mes').text('')
        $('#confirm-pass-mes').text('')
        resetInput()
    })

    // avatar opt

    $('#avatar-account').click(function () {
        $('#' + currShow).fadeOut(200, function () {
            showSection($('#your-account-btn'), 'Your Account')
        })
    })

    // message handle

    function resetMes() {
        if (currShow == 'deposit-function') {
            $('#deposit-card-number-mes').val('')
            $('#deposit-expiration-date-mes').val('')
            $('#deposit-cvv-mes').val('')
            $('#deposit-amount-mes').val('')
        }
        if (currShow == 'withdraw-function') {
            $('#withdraw-card-number-mes').val('')
            $('#withdraw-expiration-date-mes').val('')
            $('#withdraw-cvv-mes').val('')
            $('#withdraw-amount-mes').val('')
            $('#withdraw-amount-mes').val('')
        }
        if (currShow == 'transfer-function') {
            $('#transfer-phone-number').val('')
            $('#me-fee').prop('checked', true)
            $('#transfer-amount-mes').val('')
        }
        if (currShow == 'card-function') {
            $('option[value="viettel"]').prop('selected', true)
            $('option[value="10000"]').prop('selected', true)
            $('option[value="1"]').prop('selected', true)
        }
    }

    function showMes(message, type) {
        if (type == 'success') {
            $('#mes').css('backgroundColor', '#4b961c')
        } else {
            $('#mes').css('backgroundColor', '#ff084a')
        }
        $('#message').text(message)
        $('#mes').toast('show')
    }

    // deposit function handle

    $('.close-btn').click(function () {
        hideSection()
        resetMes()
    })

    function validateCardInfo(form, number, cvv, amount) {
        if ($('#permission').text() == 'user') {
            amount = Number(amount)
            const numberOnly = /^[0-9]{6,}$/
            const cvvNumber = /^[0-9]{3,}$/
            let flagCardInfo = true

            if (!numberOnly.test(number)) {
                $('#' + form + '-card-number-mes').text('Card number must contain 6 number')
                flagCardInfo = false
            } else {
                $('#' + form + '-card-number-mes').text('')
            }
            if (!cvvNumber.test(cvv)) {
                $('#' + form + '-cvv-mes').text('CVV code must contain 3 number')
                flagCardInfo = false
            } else {
                $('#' + form + '-cvv-mes').text('')
            }
            if (amount <= 0 || Number.isNaN(amount) || !Number.isInteger(amount) || amount % 50000 != 0) {
                $('#' + form + '-amount-mes').text('Invalid amount')
                flagCardInfo = false
            } else {
                $('#' + form + '-amount-mes').text('')
            }
            return flagCardInfo
        }
    }

    $('#deposit-btn').click(function () {
        const cardNumber = $('#deposit-card-number').val()
        const expirationDate = $('#deposit-expiration-date').val()
        const cvvCode = $('#deposit-cvv-code').val()
        const depositAmount = $('#deposit-amount').val()
        if (validateCardInfo('deposit', cardNumber, cvvCode, depositAmount)) {
            $.ajax({
                url: '../api/user/deposit.php',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    email: email,
                    cardnumber: cardNumber,
                    expdate: expirationDate,
                    cvv: cvvCode,
                    amount: Number(depositAmount)
                })
            })
                .done(function (data) {
                    if (data.code == 0) {
                        updateHistory()
                        getUserDetail()
                        showMes(data.data, 'success')
                    } else {
                        showMes(data.data, 'error')
                    }
                })
        }
    })

    // withdraw handle

    $('#withdraw-btn').click(function () {
        const cardNumber = $('#withdraw-card-number').val()
        const expirationDate = $('#withdraw-expiration-date').val()
        const cvvCode = $('#withdraw-cvv-code').val()
        const withdrawAmount = $('#withdraw-amount').val()
        const note = $('#withdraw-mes').val()
        if (validateCardInfo('withdraw', cardNumber, cvvCode, withdrawAmount)) {
            $.ajax({
                url: '../api/user/withdraw.php',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    email: email,
                    cardnumber: cardNumber,
                    expdate: expirationDate,
                    cvv: cvvCode,
                    amount: Number(withdrawAmount),
                    note: note
                })
            })
                .done(function (data) {
                    if (data.code == 0) {
                        updateHistory()
                        getUserDetail()
                        showMes(data.data, 'success')
                    } else {
                        showMes(data.data, 'error')
                    }
                })
        }
    })

    // transfer handle

    function checkPhone(number) {
        const phoneRegex = /^[0-9]{10,}$/
        if (!phoneRegex.test(number)) {
            $('#transfer-phone-number-mes').text('Invalid phone number')
            return false
        } else {
            $('#transfer-phone-number-mes').text('')
        }
        return true
    }

    function validateTransfer(phone, amount) {
        if ($('#permission').text() == 'user') {
            amount = Number(amount)
            let flagTransfer = checkPhone(phone)
            if (amount <= 0 || Number.isNaN(amount) || !Number.isInteger(amount) || amount % 50000 != 0) {
                $('#transfer-amount-mes').text('Invalid amount')
                flagTransfer = false
            } else {
                $('#transfer-amount-mes').text('')
            }
            return flagTransfer
        }
    }

    function expiredOtp() {
        $.ajax({
            url: '../api/user/getotp.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email,
            })
        })

        let time = 60
        const countDown = setInterval(function () {
            time -= 1
            $('#otp-countdown').text(time + 's')
            if (time <= 0) {
                clearInterval(countDown)
                $('#otp-countdown').text('Resend')
                $('#otp-countdown').css('cursor', 'pointer')
                $('#otp-countdown').click(function () {
                    resetOtp()
                    expiredOtp()
                    $(this).off('click')
                })
            }
        }, 1000)

        $('#verify-btn').click(function () {
            clearInterval(countDown)
        })

        $('#close-otp-form').click(function () {
            clearInterval(countDown)
        })
    }

    function resetOtp() {
        $('#otp').val('')
    }

    $('#otp').change(function () {
        if ($('#otp').val().length != 6) {
            $('#otp-mess').text('Invalid OTP code')
        } else {
            $('#otp-mess').text('')
        }
    })

    function otpInput() {
        let inputFlag = false
        const otp = $('#otp').val()
        if (otp == '') {
            return false
        }
        $.ajax({
            url: '../api/user/verifyotp.php',
            type: 'POST',
            async: false,
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email,
                otp_code: otp
            })
        })
            .done(function (data) {
                if (data.code == 0) {
                    showMes(data.data, 'success')
                    $('#otp-modal').hide()
                    inputFlag = true
                } else {
                    showMes(data.data, 'error')
                    inputFlag = false
                }
                resetOtp()
            })
        return inputFlag
    }

    $('#transfer-btn').click(function () {
        const phone = $('#transfer-phone-number').val()
        const fee = $('input[name="fee"]').val()
        const amount = $('#transfer-amount').val()
        const note = $('#transfer-mes').val()
        if (validateTransfer(phone, amount)) {
            const otpModal = new bootstrap.Modal(document.getElementById('otp-modal'))
            $('#otp-mes').text('Code is sent to ' + email)
            expiredOtp()
            otpModal.show()
            $('#verify-btn').click(function () {
                const result = otpInput()
                if (result) {
                    $.ajax({
                        url: '../api/user/transfer.php',
                        type: 'POST',
                        contentType: 'application/json',
                        dataType: 'json',
                        data: JSON.stringify({
                            email: email,
                            receiver: phone,
                            amount: amount,
                            feepaid: fee,
                            note: note
                        })
                    })
                        .done(function (data) {
                            if (data.code == 0) {
                                updateHistory()
                                getUserDetail()
                                showMes(data.data, 'success')
                            } else {
                                showMes(data.data, 'error')
                            }
                        })
                }
                otpModal.hide()
            })
        }
    })

    $('#transfer-phone-number').keyup(function () {
        const phone = $(this).val()
        if (checkPhone(phone)) {
            $.ajax({
                url: '../api/user/userdetail.php',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    email: email,
                    phone: phone
                })
            })
                .done(function (data) {
                    if (data.code == 0) {
                        $('#receiver-name').text(data.data.name)
                    } else {
                        showMes(data.data, 'error')
                    }
                })
        }
    })

    // card handle

    $('#card-btn').click(function () {
        const cardNetwork = $('#card-network').val()
        const cardPrice = $('#card-price').val()
        const quantity = $('#card-quantity').val()
        $.ajax({
            url: '../api/user/topupcard.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email,
                networkname: cardNetwork,
                price: Number(cardPrice),
                quantity: Number(quantity)
            })
        })
            .done(function (data) {
                if (data.code == 0) {
                    updateHistory()
                    getUserDetail()
                    showMes(data.data, 'success')
                } else {
                    showMes(data.data, 'error')
                }
            })
    })

    // user info

    function updateUserInfo(userInfo) {
        if (userInfo.idState == 6) {
            $('#update-btn').show()
            $('#status-icon').removeClass('fa-circle-check')
            $('#status-icon').addClass('fa-circle-exclamation')
        } else {
            $('#update-btn').hide()
            $('#status-icon').addClass('fa-circle-check')
            $('#status-icon').removeClass('fa-circle-exclamation')
        }
        if (userInfo.idState == 3) {
            showMes("Tài khoản này đã bị vô hiệu hóa, vui lòng liên hệ tổng đài 18001008", "error")
        }
        $('#user-name').text(userInfo.name)
        $('#user-id').text(userInfo.username)
        $('#name.account-info').text(userInfo.name)
        $('#birthday.account-info').text(userInfo.birthday)
        $('#phone-number.account-info').text(userInfo.phone)
        $('#email.account-info').text(userInfo.email)
        $('#address.account-info').text(userInfo.address)
    }

    function updateBalance(value) {
        $('.wallet-balance').text(numberWithDot(value) + ' đ')
    }

    function getUserDetail() {
        $.ajax({
            url: '../api/user/userdetail.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email
            })
        })
            .done(function (data) {
                updateUserInfo(data.data)
                updateBalance(data.data.balance)
            })
    }

    if ($('#permission').text() == 'user' || $('#permission').text() == 'new') {
        getUserDetail()
    }

    if ($('#permission').text() == 'user') {
        $.ajax({
            url: '../api/user/userdetail.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email
            })
        })
            .done(function (data) {
                const userInfo = data.data
                if (userInfo.idState == 6) {
                    $('#update-btn').show()
                }
                $('#user-name').text(userInfo.name)
                $('#user-id').text(userInfo.username)
                $('#name.account-info').text(userInfo.name)
                $('#birthday.account-info').text(userInfo.birthday)
                $('#phone-number.account-info').text(userInfo.phone)
                $('#email.account-info').text(userInfo.email)
                $('#address.account-info').text(userInfo.address)
            })
    }

    // reset password

    $('#reset-pass-btn').click(function () {
        const currPass = $('#input-current-password').val()
        const newPass = $('#input-new-password').val()
        const confirmNewPas = $('#confirm-password').val()
        $.ajax({
            url: '../api/user/changepwd.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                email: email,
                curr_pass: currPass,
                new_pass: newPass,
                confirm_pass: confirmNewPas
            })
        })
            .done(function (data) {
                if (data.code == 0) showMes(data.data, 'success')
                else showMes(data.data, 'error')
            })
    })

    // account management

    function showDialog(modal, title) {
        $('#confirm-dialog .modal-title').text(title)
        const verifyModal = new bootstrap.Modal(document.getElementById('confirm-dialog'))
        $('#cancel-btn').click(function () {
            modal.show()
            verifyModal.hide()
        })
        $('#accept-btn').click(function () {
            modal.hide()
            verifyModal.hide()
        })
        verifyModal.show()
    }

    function getAccManage() {
        $.ajax({
            url: '../api/admin/userlist.php',
            type: 'POST',
            async: false,
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                idState: 0
            })
        })
            .done(function (data) {
                $('#activate-acc-list').html('')
                data.data.forEach(user => {
                    $('#activate-acc-list').append(
                        `<tr userid="${user.id}" class="user-detail">` +
                        `<td>${user.id}</td>` +
                        `<td>${user.name}</td>` +
                        `<td>${user.phone}</td>` +
                        `<td>${user.email}</td>` +
                        // `<td>${user.birthday}</td>` + 
                        `</tr>`
                    )
                })
            })
        $.ajax({
            url: '../api/admin/userlist.php',
            type: 'POST',
            async: false,
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                idState: 1
            })
        })
            .done(function (data) {
                $('#actived-acc-list').html('')
                data.data.forEach(user => {
                    $('#actived-acc-list').append(
                        `<tr userid="${user.id}" useremail="${user.email}" class="user-detail">` +
                        `<td>${user.id}</td>` +
                        `<td>${user.name}</td>` +
                        `<td>${user.phone}</td>` +
                        `<td>${user.email}</td>` +
                        // `<td>${user.birthday}</td>` +
                        `</tr>`
                    )
                })
            })
        $.ajax({
            url: '../api/admin/userlist.php',
            type: 'POST',
            async: false,
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                idState: 4
            })
        })
            .done(function (data) {
                $('#disable-acc-list').html('')
                data.data.forEach(user => {
                    $('#disable-acc-list').append(
                        `<tr userid="${user.id}" class="user-detail">` +
                        `<td>${user.id}</td>` +
                        `<td>${user.name}</td>` +
                        `<td>${user.phone}</td>` +
                        `<td>${user.email}</td>` +
                        // `<td>${user.birthday}</td>` +
                        `</tr>`
                    )
                })
            })
        $.ajax({
            url: '../api/admin/userlist.php',
            type: 'POST',
            async: false,
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                idState: 2
            })
        })
            .done(function (data) {
                $('#locked-acc-list').html('')
                data.data.forEach(user => {
                    $('#locked-acc-list').append(
                        `<tr userid="${user.id}" class="user-detail">` +
                        `<td>${user.id}</td>` +
                        `<td>${user.name}</td>` +
                        `<td>${user.phone}</td>` +
                        `<td>${user.email}</td>` +
                        // `<td>${user.birthday}</td>` +
                        `</tr>`
                    )
                })
            })
        $('.user-detail').click(function () {
            const id = $(this).attr('userid')
            const email = $(this).attr('useremail')
            $.ajax({
                url: '../api/admin/userdetail.php',
                type: 'POST',
                async: false,
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    id: id
                })
            })
                .done(function (data) {
                    const user = data.data
                    const userModal = new bootstrap.Modal(document.getElementById('user-detail-modal'))
                    $('#user-id').text(user.id)
                    $('#user-name').text(user.name)
                    $('#user-phone').text(user.phone)
                    $('#user-email').text(user.email)
                    $('#user-birthday').text(user.birthday)
                    $('#user-address').text(user.address)
                    $('#user-front').attr('src', `../uploads/${user.front}`)
                    $('#user-back').attr('src', `../uploads/${user.back}`)
                    if (user.idState == 0) {
                        $('#unlock-btn-wrap').hide()
                        $('#trans-his-btn-wrap').hide()
                        $('#verify-btn-group').show()
                        $('#verify-btn').click(function () {
                            showDialog(userModal, 'Verify this account?')
                            $('#accept-btn').click(function () {
                                $.ajax({
                                    url: '../api/admin/xacminh.php',
                                    type: 'POST',
                                    contentType: 'application/json',
                                    dataType: 'json',
                                    data: JSON.stringify({
                                        id: id
                                    })
                                })
                                    .done(function (data) {
                                        if (data.code == 0) {
                                            getAccManage()
                                            showMes(data.data, 'success')
                                        } else {
                                            showMes(data.data, 'error')
                                        }
                                    })
                            })
                        })
                        $('#update-info-btn').click(function () {
                            showDialog(userModal, 'Update this account?')
                            $('#accept-btn').click(function () {
                                $.ajax({
                                    url: '../api/admin/bosung.php',
                                    type: 'POST',
                                    contentType: 'application/json',
                                    dataType: 'json',
                                    data: JSON.stringify({
                                        id: id
                                    })
                                })
                                    .done(function (data) {
                                        if (data.code == 0) {
                                            getAccManage()
                                            showMes(data.data, 'success')
                                        } else {
                                            showMes(data.data, 'error')
                                        }
                                    })
                            })
                        })
                        $('#cancel-btn').click(function () {
                            showDialog(userModal, 'Cancel this account?')
                            $('#accept-btn').click(function () {
                                $.ajax({
                                    url: '../api/admin/huy.php',
                                    type: 'POST',
                                    contentType: 'application/json',
                                    dataType: 'json',
                                    data: JSON.stringify({
                                        id: id
                                    })
                                })
                                    .done(function (data) {
                                        if (data.code == 0) {
                                            getAccManage()
                                            showMes(data.data, 'success')
                                        } else {
                                            showMes(data.data, 'error')
                                        }
                                    })
                            })
                        })
                    }
                    if (user.idState == 1) {
                        $('#verify-btn-group').hide()
                        $('#unlock-btn-wrap').hide()
                        $('#trans-his-btn-wrap').show()
                        $('#trans-his-btn').click(function () {
                            $('#trans-his-list').html('')
                            const transHisModal = new bootstrap.Modal(document.getElementById('trans-his-modal'))
                            $.ajax({
                                url: '../api/user/gettrans.php',
                                type: 'POST',
                                async: false,
                                contentType: 'application/json',
                                dataType: 'json',
                                data: JSON.stringify({
                                    email: email
                                })
                            })
                                .done(function (data) {
                                    data.data.forEach(history => {
                                        const icon = transIcon(history.transtype)
                                        const statusColor = transStatus(history.approval).statusColor
                                        const status = transStatus(history.approval).status
                                        $('#trans-his-list').append(
                                            `<tr class="history-item">` +
                                            `<td class="d-flex align-items-center">` +
                                            `<i` +
                                            ` class="fa-solid ${icon} transaction-icon me-3"></i>` +
                                            `${history.transtype}` +
                                            `</td>` +
                                            `<td style="color: ${statusColor}">${status}</td>` +
                                            `<td>${history.datetrans}</td>` +
                                            `<td>${numberWithDot(history.amount)} đ</td>` +
                                            `</tr>`
                                        )
                                    })
                                    userModal.hide()
                                    transHisModal.show()
                                    $('#close-trans-form').click(function () {
                                        transHisModal.hide()
                                        userModal.show()
                                    })
                                })
                        })
                    }
                    if (user.idState == 4) {
                        $('#verify-btn-group').hide()
                        $('#trans-his-btn-wrap').hide()
                        $('#unlock-btn-wrap').show()
                        $('#unlock-btn').click(function () {
                            showDialog(userModal, 'Unlock this account?')
                            $('#accept-btn').click(function () {
                                $.ajax({
                                    url: '../api/admin/mokhoa.php',
                                    type: 'POST',
                                    contentType: 'application/json',
                                    dataType: 'json',
                                    data: JSON.stringify({
                                        id: id
                                    })
                                })
                                    .done(function (data) {
                                        if (data.code == 0) {
                                            getAccManage()
                                            showMes(data.data, 'success')
                                        } else {
                                            showMes(data.data, 'error')
                                        }
                                    })
                            })
                        })
                    }
                    userModal.show()
                    $('#close-detail-form').click(function () {
                        userModal.hide()
                    })
                })
        })
    }

    if ($('#permission').text() == 'admin') {
        getAccManage()
    }

    // transaction manage

    function getApproval() {
        $.ajax({
            url: '../api/admin/getapprovaltrans.php',
            type: 'POST',
            async: 'false',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                permission: 'admin'
            })
        })
            .done(function (data) {
                const transaction = data.data
                $('#confirm-transaction-list').html('')
                transaction.forEach(value => {
                    $('#confirm-transaction-list').append(
                        `<tr idtrans="${value.idtrans}" email="${value.email}" type="${value.transtype}">` +
                        `<td>${value.idtrans}</td>` +
                        `<td>${value.transtype}</td>` +
                        `<td>${value.datetrans}</td>` +
                        `<td>${value.amount}</td>` +
                        // `<td>` +
                        // `<button class="btn btn-outline-success transaction-accept me-2">Approval</button>` +
                        // `<button  class="btn btn-outline-danger transaction-cancel">Cancel</button>` +
                        // `</td>` +
                        `</tr>`
                    )
                })
                $('.transaction-accept')?.click(function () {
                    const id = $(this).parents('tr').attr('idtrans')
                    const transEmail = $(this).parents('tr').attr('email')
                    const transType = $(this).parents('tr').attr('type')
                    $.ajax({
                        url: '../api/admin/approvaltrans.php',
                        type: 'POST',
                        async: 'false',
                        contentType: 'application/json',
                        dataType: 'json',
                        data: JSON.stringify({
                            email: transEmail,
                            idtrans: id,
                            transtype: transType,
                            decision: 1
                        })
                    })
                        .done(function (data) {
                            showMes(data.data, 'success')
                            getApproval()
                        })
                })
                $('.transaction-cancel')?.click(function () {
                    const id = $(this).parents('tr').attr('idtrans')
                    const transEmail = $(this).parents('tr').attr('email')
                    const transType = $(this).parents('tr').attr('type')
                    $.ajax({
                        url: '../api/admin/approvaltrans.php',
                        type: 'POST',
                        async: 'false',
                        contentType: 'application/json',
                        dataType: 'json',
                        data: JSON.stringify({
                            email: transEmail,
                            idtrans: id,
                            transtype: transType,
                            decision: 0
                        })
                    })
                        .done(function (data) {
                            showMes(data.data, 'success')
                            getApproval()
                        })
                })
            })
    }

    $('#confirm-transaction-btn').click(function () {
        getApproval()
    })

    $('#forgetPassword').submit(function (e) {
        e.preventDefault()
        var email = $('input[name=email]').val()
        var phone = $('input[name=phone]').val()
        let data = {
            email: email,
            phone: phone
        };
        $.ajax({
            url: '/api/user/getotp.php',
            type: "POST",
            async: false,
            data: JSON.stringify(data),
            dataType: 'json',
            contentType: 'application/json',
            success: function (response) {
                const data = JSON.parse(JSON.stringify(response))
                if (data) {
                    if (data['code'] == 0) {
                        window.location = '/verifyotp.php'
                    } else {
                        alert(data['data'])
                    }
                }
            }
        })
    })
    $('#verifyOtp').submit(function (e) {
        e.preventDefault()
        var email = $('[name=email]').val()
        var otp = $('[name=otp]').val()
        let data = {
            email: email,
            otp_code: otp
        };
        $.ajax({
            url: '/api/user/verifyotp.php',
            type: "POST",
            data: JSON.stringify(data),
            dataType: 'json',
            contentType: 'application/json',
            success: function (response) {
                const data = JSON.parse(JSON.stringify(response))
                console.log(data);
                if (data) {
                    if (data['code'] == 0) {
                        window.location = '/changePassword.php'
                    } else {
                        alert(data['data'])
                    }
                }
            }
        })
    })
})