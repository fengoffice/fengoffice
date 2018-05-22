<script id="confirm-dialog-box" type="text/x-handlebars-template">
    <div id="confirm-dialog-box{{genid}}" class="modal-container" style="background-color: white;padding: 10px;">

            <div style="display: inline-block; width: 100%; min-width: 200px" id="{{genid}}addwork">
                <input type="hidden" value="{{taskId}}" name="object_id">

                <div style="font-size:20px;font-weight:bolder;color:#666;margin:5px;">
                    {{text}}
                </div>

                <div class="clear"></div>
                <div style="float:right;margin-left:10px;">
                    <button class="submit blue" onclick="{{cancel_fn}}" tabindex="90" style="">{{lang 'cancel'}}</button>
                </div>
                <div style="float:right;margin-left:10px;">
                    <button class="submit blue" onclick="{{accept_fn}}" tabindex="90" style="">{{lang 'ok'}}</button>
                </div>
            </div>

    </div>
</script>
