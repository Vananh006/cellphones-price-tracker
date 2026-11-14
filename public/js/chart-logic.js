$(document).ready(function () {
    const canvas = document.getElementById('priceChart');
    let myPriceChart = null;
    let currentDays = 30;

    const formatVnd = function (value) {
        if (value === null || value === undefined || isNaN(value)) {
            return '--';
        }

        return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
    };

    const setChartState = function (message, type = 'muted') {
        const $state = $('#priceChartState');

        if (!$state.length) {
            return;
        }

        $state
            .removeClass('text-muted text-danger text-success')
            .addClass(type === 'danger' ? 'text-danger' : type === 'success' ? 'text-success' : 'text-muted')
            .text(message);
    };

    const hasChartData = function (fptPrices, cpsPrices) {
        const fptHasData = (fptPrices || []).some(price => price !== null && price !== undefined);
        const cpsHasData = (cpsPrices || []).some(price => price !== null && price !== undefined);

        return fptHasData || cpsHasData;
    };

    const updateSummary = function (summary) {
        if (!summary) {
            $('#priceChartSummary').hide();
            return;
        }

        if (summary.best_now) {
            $('#summaryBestNow').text(
                formatVnd(summary.best_now.price) + ' - ' + summary.best_now.store
            );
        } else {
            $('#summaryBestNow').text('--');
        }

        if (summary.lowest_history) {
            $('#summaryLowest').text(
                formatVnd(summary.lowest_history.price) +
                ' - ' +
                summary.lowest_history.store +
                ' (' +
                summary.lowest_history.date +
                ')'
            );
        } else {
            $('#summaryLowest').text('--');
        }

        $('#summaryUpdated').text(summary.last_updated || '--');
        $('#priceChartSummary').show();
    };

    const renderChart = function (labels, dataFPT, dataCPS) {
        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        const ctx = canvas.getContext('2d');

        if (myPriceChart) {
            myPriceChart.destroy();
        }

        myPriceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels || [],
                datasets: [
                    {
                        label: 'FPT Shop',
                        data: dataFPT || [],
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.08)',
                        borderWidth: 3,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        spanGaps: true
                    },
                    {
                        label: 'CellphoneS',
                        data: dataCPS || [],
                        borderColor: '#e11d48',
                        backgroundColor: 'rgba(225, 29, 72, 0.08)',
                        borderWidth: 3,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        spanGaps: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                normalized: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 18
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatVnd(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function (value) {
                                return Math.round(value / 1000000) + 'tr';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Giá bán'
                        }
                    }
                }
            }
        });
    };

    const loadPriceHistory = function (days = currentDays) {
        if (!canvas) {
            return;
        }

        const productId = canvas.getAttribute('data-product-id');
        const variantId = canvas.getAttribute('data-variant-id');

        if (!productId) {
            setChartState('Không tìm thấy mã sản phẩm để tải lịch sử giá.', 'danger');
            return;
        }

        let historyUrl = `/api/price-history/${productId}?days=${days}`;

        if (variantId) {
            historyUrl += `&variant_id=${variantId}`;
        }

        setChartState('Đang tải lịch sử giá...');

        $.ajax({
            url: historyUrl,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                renderChart(response.dates || [], response.fptPrices || [], response.cpsPrices || []);
                updateSummary(response.summary || null);

                if (!hasChartData(response.fptPrices, response.cpsPrices)) {
                    setChartState('Chưa có đủ dữ liệu lịch sử giá. Hãy chạy crawler thêm vài lần để biểu đồ có xu hướng rõ hơn.');
                    return;
                }

                setChartState('Di chuột vào biểu đồ để xem giá từng ngày.', 'success');
            },
            error: function () {
                setChartState('Không thể tải lịch sử giá. Kiểm tra route /api/price-history hoặc dữ liệu price_history.', 'danger');
            }
        });
    };

    if (canvas) {
        loadPriceHistory();

        $('#priceRangeControls [data-days]').on('click', function () {
            $('#priceRangeControls [data-days]').removeClass('active');
            $(this).addClass('active');

            currentDays = parseInt($(this).data('days'));
            loadPriceHistory(currentDays);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PHẦN CẢNH BÁO GIÁ CŨ - GIỮ LẠI ĐỂ KHÔNG ẢNH HƯỞNG CHỨC NĂNG KHÁC
    |--------------------------------------------------------------------------
    */
    $('#btnOpenAlert').click(function () {
        $('#priceAlertModal').css('display', 'flex').hide().fadeIn(200);
        $('#priceSuccess').text('');
        $('#priceError').text('');
    });

    $('.close-modal, .modal-overlay').click(function (e) {
        if (e.target === this) {
            $('#priceAlertModal').fadeOut(200);
        }
    });

    const currentPrice = parseInt($('#currentPriceRef').data('price')) || 0;

    $('#targetPriceInput').on('input', function () {
        const rawValue = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(rawValue ? parseInt(rawValue).toLocaleString('vi-VN') : '');
        $('#priceError').text('');
    });

    $('.btn-preset').click(function () {
        const discountRate = parseFloat($(this).data('discount')) || 0;
        const targetPrice = Math.round(currentPrice - currentPrice * discountRate);

        $('#targetPriceInput').val(targetPrice.toLocaleString('vi-VN'));
        $('#priceError').text('');
    });

    $('#btnSubmitAlert').click(function () {
        const productId = canvas ? canvas.getAttribute('data-product-id') : null;
        const variantId = canvas ? canvas.getAttribute('data-variant-id') : null;

        const inputVal = $('#targetPriceInput').val().replace(/[^0-9]/g, '');
        const targetPrice = parseInt(inputVal);

        if (!inputVal || !targetPrice || targetPrice <= 0) {
            $('#priceError').text('Vui lòng nhập mức giá mong muốn hợp lệ.');
            return;
        }

        if (currentPrice > 0 && targetPrice >= currentPrice) {
            $('#priceError').text('Giá mong muốn nên thấp hơn giá hiện tại.');
            return;
        }

        const $btn = $(this);
        $btn.text('Đang xử lý...').prop('disabled', true);

        $.ajax({
            url: '/api/price-alerts',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                product_id: productId,
                variant_id: variantId,
                target_price: targetPrice
            },
            success: function (response) {
                $btn.text('Đã bật cảnh báo').prop('disabled', false);
                $('#priceSuccess').text(response.message || 'Đã bật cảnh báo giá.');
                $('#priceError').text('');
                $('#btnOpenAlert').text('✅ Đang theo dõi giá');

                setTimeout(() => $('#priceAlertModal').fadeOut(200), 1600);
            },
            error: function (xhr) {
                let message = 'Có lỗi xảy ra, vui lòng thử lại.';

                if (xhr.status === 401) {
                    message = 'Bạn cần đăng nhập để sử dụng tính năng này.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                $('#priceError').text(message);
                $btn.text('Xác nhận theo dõi').prop('disabled', false);
            }
        });
    });
});
