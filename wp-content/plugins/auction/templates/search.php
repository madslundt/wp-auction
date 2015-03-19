<form method="GET" action="<?php echo $page; ?>">
    <div class="col-xs-12">
        <div class="input-group">
            <input class="form-control input-lg" maxlength="100" id="appendedInputButton" type="text" name="<?php echo Auction::QUERY_KEY_FREETEXT; ?>" value="<?php echo Auction::get_search_var(Auction::QUERY_KEY_FREETEXT, 'esc_attr,trim'); ?>" placeholder="<?php echo $placeholder; ?>" />
            <span class="input-group-btn">
                <button type="submit" class="btn btn-default btn-search btn-lg" id="searchsubmit"><?php _ex('Search','verb',Auction::DOMAIN); ?></button>
            </span>
        </div>
    </div>
</form>