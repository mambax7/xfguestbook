<{$signform.javascript}>
<script>
    function test_other(valeur) {
        if (valeur === "other") {
            document.getElementById('other_caption').style.display = "block";
            document.getElementById('other_tbox').style.display = "block";
        }
        else {
            document.getElementById('other_caption').style.display = "none";
            document.getElementById('other_tbox').style.display = "none";
        }
    }
    var result_style = document.getElementById('result_tr').style;
    result_style.display = 'table-row';
</script>

<div align="center"><span style="color: #FF0000; "><strong><{$msgstop}></strong></span></div>
<{if $preview and not $msgstop}>
    <{include file="db:xfguestbook_item.tpl"}>
<{/if}>
<div align="center"><strong>
        <{$moderate}>
    </strong></div><br>
<form name="<{$signform.name}>" action="<{$signform.action}>" method="<{$signform.method}>" <{$signform.extra}>>
    <table class="outer" cellspacing="1">
        <tr>
            <th colspan="2"><{$signform.title}></th>
        </tr>
        <!-- start of form elements loop -->
        <tr id="result_tr" style="display: none;">
            <td class="head"><{$signform.elements.uman.caption}></td>
            <td class="<{cycle values='even,odd'}>"><{$signform.elements.uman.body}></td>
        </tr>
        <tr>
            <td class="head"><{$signform.elements.name.caption}></td>
            <td class="<{cycle values='even,odd'}>"><{$signform.elements.name.body}></td>
        </tr>
        <{if $signform.elements.gender.body}>
            <tr>
                <td class="head"><{$signform.elements.gender.caption}></td>
                <td class="<{cycle values='even,odd'}>"><{$signform.elements.gender.body}></td>
            </tr>
        <{/if}>
        <{if $signform.elements.country.body}>
            <tr>
                <td class="head"><{$signform.elements.country.caption}>
                    <div style="display:none;" id="other_caption"><br><{$smarty.const.MD_XFGUESTBOOK_OTHER_CAPTION}></div>
                </td>
                <td class="<{cycle values='even,odd'}>"><{$signform.elements.country.body}>
                    <div style="display:none;" id="other_tbox"><br><{$signform.elements.other.body}></div>
                </td>
            </tr>
        <{/if}>
        <{if $signform.elements.email.body}>
            <tr>
                <td class="head"><{$signform.elements.email.caption}></td>
                <td class="<{cycle values='even,odd'}>"><{$signform.elements.email.body}></td>
            </tr>
            <tr>
                <td class="head"><{$signform.elements.url.caption}></td>
                <td class="<{cycle values='even,odd'}>"><{$signform.elements.url.body}></td>
            </tr>
        <{/if}>
        <tr>
            <th colspan="2"><{$smarty.const.MD_XFGUESTBOOK_HEADMSG}></th>
        </tr>
        <tr>
            <td class="head"><{$signform.elements.title.caption}></td>
            <td class="<{cycle values='even,odd'}>"><{$signform.elements.title.body}></td>
        </tr>
        <tr>
            <td class="head"><{$signform.elements.message.caption}>
                <{if $nofollow}>
                    <br>
                    <br>
                    <{$smarty.const.MD_XFGUESTBOOK_NOFOLLOW_MSG}>
                <{/if}>
            </td>
            <td class="<{cycle values='even,odd'}>"><{$signform.elements.message.body}></td>
        </tr>
        <{if $signform.elements.photo.body}>
            <tr>
                <td class="head"><{$signform.elements.photo.caption}></td>
                <td class="<{cycle values='even,odd'}>"><{$signform.elements.photo.body}></td>
            </tr>
        <{/if}>
        <{if $confirm_image}>
            <tr>
                <td class="head"><{$smarty.const.MD_XFGUESTBOOK_CONFIRM_CODE_DESC}></td>
                <td class="<{cycle values='even,odd'}>"><{$confirm_image}>
            </tr>
            <tr>
                <td class="head"><{$smarty.const.MD_XFGUESTBOOK_CONFIRM_CODE}></td>
                <td class="<{cycle values='even,odd'}>"><{$signform.elements.confirm_code.body}>
            </tr>
            <{$signform.elements.confirm_str.body}>
        <{/if}>
        <tr>
            <td class="head"><{$signform.elements.button.caption}></td>
            <td class="<{cycle values='even,odd'}>"><{$signform.elements.button.body}></td>
        </tr>
        <!-- end of form elements loop -->
        <{$signform.elements.preview_name.body}>
        <{$signform.elements.user_id.body}>
        <{$signform.elements.skipValidationJS.body}>
    </table>
</form>
