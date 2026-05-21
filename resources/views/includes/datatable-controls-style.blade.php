<style>
    .dataTables_length label,
    .dataTables_filter label,
    .dataTables_length select,
    .dataTables_filter input { font-size: 14px !important; }
    .dataTables_length select {
        height: 36px !important;
        width: 75px !important;
        padding: 4px 8px !important;
        background-image: none !important;
        -webkit-appearance: auto !important;
        appearance: auto !important;
    }
    .dataTables_filter input {
        height: 36px !important;
        padding: 4px 10px !important;
        border-radius: 6px !important;
        border: 1px solid #ced4da !important;
    }
    .dt-buttons { display: flex !important; align-items: center !important; gap: 6px !important; }
    .dt-buttons .btn {
        height: 38px !important;
        font-size: 14px !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    div.dataTables_wrapper { position: relative; }
    div.dataTables_processing {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        z-index: 30 !important;
        width: auto !important;
        min-width: 170px !important;
        margin: 0 !important;
        transform: translate(-50%, -50%) !important;
        padding: 12px 18px 12px 46px !important;
        border: 1px solid rgba(226, 232, 240, 0.95) !important;
        border-radius: 999px !important;
        background: rgba(255, 255, 255, 0.98) !important;
        box-shadow: 0 14px 40px rgba(15, 23, 42, 0.16), 0 2px 8px rgba(15, 23, 42, 0.06) !important;
        color: #334155 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        letter-spacing: .01em !important;
        text-align: center !important;
        backdrop-filter: blur(8px) !important;
    }
    div.dataTables_processing:before {
        content: "" !important;
        position: absolute !important;
        left: 16px !important;
        top: 50% !important;
        width: 16px !important;
        height: 16px !important;
        margin-top: -8px !important;
        border: 2px solid #dbeafe !important;
        border-top-color: #2563eb !important;
        border-radius: 50% !important;
        animation: ams-dt-spin .75s linear infinite !important;
    }
    @keyframes ams-dt-spin {
        to { transform: rotate(360deg); }
    }
</style>
