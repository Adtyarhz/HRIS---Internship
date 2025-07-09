<div id="deletePopup-{{ $modalId }}" class="modal fade" tabindex="-1"
    aria-labelledby="deletePopupLabel-{{ $modalId }}" aria-hidden="true" style="display: none;">
    <div class="modal-dialog custom-modal-position">
        <div class="popup-delete">
            <div class="frame-1045">
                <div class="icon">
                    <span class="gg--trash"></span>
                </div>
                <div class="frame-1039">
                    <div class="are-you-sure-to-delete-this-announcement">
                        <span class="areyousuretodeletethisannouncement_span">
                            {{ $message }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="frame-167">
                <button type="button" class="button" data-bs-dismiss="modal">
                    <div class="label"><span class="label_span">Cancel</span></div>
                </button>
                <form action="{{ $action }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button_01">
                        <div class="label_01"><span class="label_01_span">Yes</span></div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.popup-delete {
    width: 640px;
    padding: 32px;
    background: #FAFBEF;
    box-shadow: 20px 20px 20px rgba(0, 0, 0, 0.08);
    border-radius: 16px;
    outline-offset: -0.50px;
    flex-direction: column;
    justify-content: center;
    align pollutant-items: center;
    gap: 40px;
    display: inline-flex;
}

.frame-1045 {
    align-self: stretch;
    height: 68px;
    justify-content: flex-start;
    align-items: flex-start;
    /* gap: 24px; */
    display: inline-flex;
    text-align: center;
}

.icon {
    width: 72px;
    height: 72px;
    position: relative;
    background: #FFEA9F;
    overflow: hidden;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.gg--trash {
  display: inline-block;
  width: 52px;
  height: 52px;
  background-repeat: no-repeat;
  border-radius: 8px;
  background-size: 100% 100%;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cg fill='%239A3B3B'%3E%3Cpath fill-rule='evenodd' d='M17 5V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V7h1a1 1 0 1 0 0-2zm-2-1H9v1h6zm2 3H7v11a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z' clip-rule='evenodd'/%3E%3Cpath d='M9 9h2v8H9zm4 0h2v8h-2z'/%3E%3C/g%3E%3C/svg%3E");
}
.frame-1039 {
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    gap: 20px;
    display: inline-flex;
}

.are-you-sure-to-delete-this-announcement {
    width: 504px;
    height: auto;
}

.areyousuretodeletethisannouncement_span {
    color: black;
    font-size: 24px;
    font-family: Inter, sans-serif;
    font-weight: 600;
    word-wrap: break-word;
}

.frame-167 {
    width: 450px;
    justify-content: flex-end;
    align-items: flex-start;
    gap: 16px;
    display: inline-flex;
}

.button {
    width: 180px;
    height: 48px;
    padding: 14px 20px;
    background: #9A3B3B;
    border-radius: 8px;
    outline-offset: -1px;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: background-color 0.2s;
}

.button:hover {
    background-color: #803030;
}

.label_span {
    color: white;
    font-size: 16px;
    font-family: Inter, sans-serif;
    font-weight: 500;
}

.button_01 {
    width: 180px;
    height: 48px;
    padding: 14px 20px;
    background: #F9FCE6;
    border-radius: 8px;
    outline-offset: -1px;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: background-color 0.2s;
}

.button_01:hover {
    background-color: #eef3c0;
}

.label_01_span {
    font-size: 16px;
    font-family: Inter, sans-serif;
    font-weight: 500;
}

.modal {
    z-index: 2000;
}

.custom-modal-position {
    margin: 0 auto;
    position: absolute; 
    top: 10%;
    left: 35%;
}
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.modal-backdrop {
    background: transparent !important;
}
</style>