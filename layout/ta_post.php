<div class="ta_box">
    <p class="ta-p" class="meta-options ta_field">
        <label class="ta-label" for="only_ta_post">Only allow Trusted Accounts to comment on this post</label>
        <input id="only_ta_post" type="checkbox" name="only_ta_post" <?php if(get_post_meta( get_the_ID(), 'only_ta_post', true )){ echo "checked"; } ?> />
    </p>
</div>