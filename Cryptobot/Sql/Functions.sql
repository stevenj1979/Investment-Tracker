DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `func_inc_var_session`() RETURNS int(11)
    NO SQL
begin
      SET @var := @var + 1;
      return @var;
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `getBTCPrice`(`Coin_ID` INT) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
Declare BTC_Price DEC(20,14);
SET BTC_Price = 0;
select `LiveCoinPrice` into BTC_Price From `CoinPrice` where `CoinID` = Coin_ID;
return BTC_Price;
END$$
DELIMITER ;
