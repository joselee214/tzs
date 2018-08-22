<style type="text/css">
.border {
    border: 1px solid #555;
    text-align: center;
}
.result{
    margin-left: 10px;
    margin-top: 10px;
}
</style>
<div class="content-notice">
    <div>Sql Query</div>
    <form action="?action=ExecuteSql" method="POST">
        <textarea style="width: 800px;height: 50px;" name="sql"><?php if(isset($this->sql)){echo $this->sql;}?></textarea>
        <div style="margin-top: 14px;">
            <input type="submit" value="query" />
        </div>
    </form>
</div>

<div class="result">
    <table class="border">
        <tr>
            <?php foreach($this->k as $k => $v){?>
            <td class="border">
                <?php echo $v;?>
            </td>
            <?php };?>
        </tr>
        <?php foreach($this->rs as $k => $v){?>
        <tr>
            <?php foreach($v as $sub_k => $sub_v){?>
            <td class="border">
                <?php echo htmlspecialchars($sub_v);?>
            </td>
            <?php };?>
        </tr>
        <?php };?>
    </table>
</div>
