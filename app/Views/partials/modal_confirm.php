<!-- ═══════════════════════════════════════════════════════ -->
<!-- CUSTOM CONFIRM MODAL COMPONENT                           -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="smms-modal-backdrop" id="smmsConfirmBackdrop">
    <div class="smms-confirm-card">
        <div class="smms-confirm-icon-wrap" id="smmsConfirmIconWrap">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        </div>
        <h3 class="smms-confirm-title" id="smmsConfirmTitle">Hapus Data?</h3>
        <p class="smms-confirm-desc" id="smmsConfirmDesc">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="smms-confirm-actions">
            <button class="smms-btn-cancel" type="button" onclick="tutupConfirmDialog()">Batal</button>
            <button class="smms-btn-danger" type="button" id="smmsConfirmBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                <span id="smmsConfirmBtnText">Hapus Data</span>
            </button>
        </div>
    </div>
</div>
