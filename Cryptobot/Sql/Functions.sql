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

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `AvgMaxPrice`(`Coin_ID` INT, `nPct` DECIMAL(20,14)) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
Declare avgPrice DEC(20,14);
Declare avg6Month DEC(20,14);
Declare avg3Month DEC(20,14);
Declare avgAllTime DEC(20,14);
Declare reducePct DEC(20,14);
Set avgPrice = 0.0;
Set avg6Month = 0.0;
Set avg3Month = 0.0;
Set avgAllTime = 0.0;
Set reducePct = 0.0;

SELECT `3MonthPrice` INTO avg3Month FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = 'High';

SELECT `6MonthPrice` INTO avg6Month FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = 'High';

Set avgPrice = ((avg6Month + avg3Month) /2);

Return avgPrice;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `AvgMinPrice`(`Coin_ID` INT, `nPct` DECIMAL(20,14)) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
Declare avgPrice DEC(20,14);
Declare avg6Month DEC(20,14);
Declare avg3Month DEC(20,14);
Declare avgAllTime DEC(20,14);
Declare addPct DEC(20,14);
Set avgPrice = 0.0;
Set avg6Month = 0.0;
Set avg3Month = 0.0;
Set avgAllTime = 0.0;
Set addPct = 0.0;

SELECT `3MonthPrice` INTO avg3Month FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = 'Low';

SELECT `6MonthPrice` INTO avg6Month FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = 'Low';

Set avgPrice = ((avg6Month+avg3Month)/2);

Return avgPrice;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `getCoinAllocation`(`nCoin` VARCHAR(15), `User_ID` INT, `nOverride` INT) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN

Declare LowMarket_ModeEnabled INT;
Declare PctOn_Low DEC(20,14);
Declare Coin_Alloc DEC(20,14);
Declare Trans_Open DEC(20,14);
DECLARE finalAlloc DEC(20,14);
Declare nSaving DEC(20,14);
DECLARE nMultiplier DEC(20,14);

SELECT `LowMarketModeEnabled` INTO LowMarket_ModeEnabled FROM `View16_CoinAllocation`  WHERE `UserID` = User_ID;
SELECT `PctOnLow` INTO PctOn_Low FROM `View16_CoinAllocation`  WHERE `UserID` = User_ID;


if nCoin = "USDT" THEN
	SELECT `USDTAlloc` into Coin_Alloc FROM `View16_CoinAllocation`  WHERE `UserID` = User_ID Limit 1;
    SELECT `SavingUSDT` into nSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;
    SET nMultiplier = 1;
ELSEIF nCoin = "BTC" THEN
    SELECT getBTCPrice(84) into nMultiplier;
	  SELECT `BTCAlloc` into Coin_Alloc FROM `View16_CoinAllocation`  WHERE `UserID` = User_ID Limit 1;
    SELECT `SavingBTC`* nMultiplier into nSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;

ELSE
    SELECT getBTCPrice(85) into nMultiplier;
	  SELECT `ETHAlloc` into Coin_Alloc FROM `View16_CoinAllocation`  WHERE `UserID` = User_ID Limit 1;
    SELECT `SavingETH`* nMultiplier into nSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;

END IF;

SELECT ifnull(sum(`CoinPrice`*`Amount`)*nMultiplier,0) into Trans_Open
              FROM `View15_OpenTransactions` WHERE `UserID` = User_ID and `StatusTr` in ('Open','Pending') and `BaseCurrency` = nCoin;

if LowMarket_ModeEnabled > 0 AND PctOn_Low > 0 THEN
    set finalAlloc = (Coin_Alloc / 100)*(PctOn_Low/(6-LowMarket_ModeEnabled))-Trans_Open - nSaving;
elseif LowMarket_ModeEnabled < 0 AND (100-PctOn_Low) > 0 THEN
    set finalAlloc = (Coin_Alloc / 100)*((100-PctOn_Low)) - Trans_Open - nSaving;
elseif LowMarket_ModeEnabled = 0 THEN
    set finalAlloc = Coin_Alloc - Trans_Open - nSaving;
else
    set finalAlloc = 0;
End if;
if nOverride = 1 THEN
	set finalAlloc = Coin_Alloc - Trans_Open - nSaving;
END if;
return finalAlloc;
END$$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `GetSaving`(`Base_curr` VARCHAR(50), `User_ID` INT) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
Declare ret_Decimal Dec(20,14);
Declare ret_USDT Dec(20,14);
Declare ret_BTC Dec(20,14);
Declare ret_ETH Dec(20,14);

if Base_curr = 'USDT' THEN
 SELECT `SavingUSDT` into ret_USDT FROM `UserCoinSavings` WHERE `UserID` = User_ID;
 SET  ret_Decimal = ret_USDT;
ELSEIF Base_curr = 'BTC' THEN
 SELECT `SavingBTC` into ret_BTC FROM `UserCoinSavings` WHERE `UserID` = User_ID;
  SET  ret_Decimal = ret_BTC;
ELSEIF Base_curr = 'ETH' THEN
 SELECT `SavingETH` into ret_ETH FROM `UserCoinSavings` WHERE `UserID` = User_ID;
  SET  ret_Decimal = ret_ETH;
end if;
Return ret_Decimal;
END$$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `getNewCoinAllocation`(`User_ID` INT, `nMode` INT, `BaseCurr` VARCHAR(50), `nOverride` INT, `saving_Override` INT, `SpreadBet_RuleID` INT) RETURNS decimal(20,14)
    NO SQL
BEGIN
Declare finalAmount DEC(20,14);
DECLARE totalSaving DEC(20,14);
DECLARE totalHolding DEC(20,14);
DECLARE Coin_ID INT;
DECLARE BTC_Price DEC(20,14);
DECLARE holdingAmount DEC(20,14);


SELECT `Total` into totalHolding FROM `BittrexBalances` WHERE `UserID` = User_ID and `Symbol` = BaseCurr;

if (BaseCurr = 'USDT') THEN
	SELECT `SavingUSDT` into totalSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;
    SELECT `HoldingUSDT` into holdingAmount FROM `UserCoinSavings` WHERE `UserID` = User_ID;
    SET Coin_ID = 83;
ELSEIF (BaseCurr = 'BTC') THEN
	SELECT `SavingBTC` into totalSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;
        SELECT `HoldingBTC` into holdingAmount FROM `UserCoinSavings` WHERE `UserID` = User_ID;
        SET Coin_ID = 84;
ELSE
	SELECT `SavingETH` into totalSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;
        SELECT `HoldingETH` into holdingAmount FROM `UserCoinSavings` WHERE `UserID` = User_ID;
        SET Coin_ID = 85;
end if;
SELECT getBTCPrice(Coin_ID) into BTC_Price;
if (nOverride = 1) THEN
	if (saving_Override = 1) THEN
		SELECT (totalHolding) into finalAmount;
    else
	    SELECT (totalHolding-totalSaving) into finalAmount;
    end if;
ELSE
    if (nMode > 0) THEN
    SELECT sum(`Amount`) as `Amount` into finalAmount FROM `UserCoinAllocationAmounts` WHERE `CoinAllocationID` <= nMode and `BaseCurrency` = BaseCurr and `UserID` = User_ID;
    	if (saving_Override = 1) THEN
    		SET finalAmount = finalAmount - holdingAmount;
        ELSE
        	SET finalAmount = finalAmount - holdingAmount - totalSaving;
        end if;
	ELSE
 	   SET finalAmount = 0;
    end if;
end if;

if SpreadBet_RuleID > 0 Then
  SET finalAmount = totalHolding - totalSaving;
end if;

return (finalAmount*BTC_Price);
END$$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `getHighLowPricebyMonth`(`Coin_ID` INT, `High_Low` VARCHAR(50), `nMonths` INT) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
Declare returnVal DEC(20,14);
SET returnVal = 0;
if (High_Low = 'High') Then
SELECT MAX(`MaxPrice`) into returnVal FROM `MonthlyMaxPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL nMonths MONTH);
ELSE
SELECT MIN(`MinPrice`) into returnVal FROM `MonthlyMinPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL nMonths MONTH);
end if;
return returnVal;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `getTotalHolding`(`Base_Currency` VARCHAR(50), `User_ID` INT) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
DECLARE Coin_ID INT;
DECLARE total_holding DEC(20,14);
DECLARE total_reserve DEC(20,14);
DECLARE total_saving DEC(20,14);
DECLARE nMultiplier DEC(20,14);

if (Base_Currency = 'USDT') THEN
SELECT `SavingUSDT` into total_saving  FROM `UserCoinSavings` WHERE `UserID` = User_ID;
ELSEIF (Base_Currency = 'BTC') THEN
SELECT  `SavingBTC` into total_saving FROM `UserCoinSavings` WHERE  `UserID` = User_ID;
ELSEIF (Base_Currency = 'ETH') THEN
SELECT  `SavingETH` into total_saving FROM `UserCoinSavings` WHERE  `UserID` = User_ID;

end if;

SELECT `ID` into Coin_ID from `Coin` where `Symbol` like Base_Currency and `BuyCoin` = 1 and `BaseCurrency` like 'USD%' limit 1;
Select getBTCPrice(Coin_ID) into nMultiplier;
SELECT ifnull(sum(`CoinPrice` * `Amount`*getBTCPrice(Coin_ID)),0) into total_holding FROM `Transaction` `Tr` join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID` WHERE `Cn`.`BaseCurrency` = Base_Currency and `Tr`.`Status` in ('Open','Pending') and `UserID` = User_ID;
SELECT `Total`*getBTCPrice(Coin_ID) into total_reserve FROM `BittrexBalances` WHERE `Symbol` = Base_Currency and `UserID` = User_ID;

return total_holding + total_reserve - (total_saving*nMultiplier);

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `GetTaxAmount`(`Start_Year` INT, `End_Year` INT, `BCurrency` VARCHAR(50), `User_ID` INT) RETURNS decimal(14,10)
    NO SQL
BEGIN
DECLARE nMultiply DEC(14,10);
DECLARE nReturn DEC(14,10);
if (BCurrency = 'USDT') then
 set nMultiply = getBTCPrice(83);
ELSEIF (BCurrency = 'BTC') then
 set nMultiply = getBTCPrice(84);
elseif (BCurrency = 'ETH') then
 set nMultiply = getBTCPrice(85);
end if;
SELECT
sum((`Tr`.`Amount` *`Ba`.`SellPrice`- `Tr`.`CoinPrice`* `Tr`.`Amount`))* nMultiply
into nReturn
FROM `Transaction` `Tr`
join `BittrexAction` `Ba` on `Ba`.`TransactionID` = `Tr`.`ID` and `Ba`.`Type` in ('Sell','SpreadSell')
join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`

WHERE `Tr`.`OrderDate` > makedate(Start_Year,95) and `Tr`.`Status` = 'Sold' and `Cn`.`BaseCurrency` = BCurrency and `Tr`.`OrderDate` < makedate(End_Year,95) and `Tr`.`UserID` = User_ID;
return nReturn;
End$$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `getPriceBySymbol`(`nSymbol` VARCHAR(50), `Base_Curr` VARCHAR(50)) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN

Declare Coin_ID INT;
Declare nPrice DEC(20,14);

Set nPrice = 0;

SELECT `ID` into Coin_ID FROM `Coin` where `Symbol` = nSymbol and `BaseCurrency` = Base_Curr and `BuyCoin` = 1;

Select `LiveCoinPrice` into nPrice From `CoinPrice` where `CoinID` = Coin_ID;

return nPrice;

END$$
DELIMITER ;
