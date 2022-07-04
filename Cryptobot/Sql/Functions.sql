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
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `getNewCoinAllocation`(`User_ID` INT, `nMode` INT, `BaseCurr` VARCHAR(50), `nOverride` INT) RETURNS decimal(20,14)
    NO SQL
BEGIN
Declare finalAmount DEC(20,14);


if (nOverride = 1) THEN
SELECT sum(`Amount`) as `Amount` into finalAmount FROM `UserCoinAllocationAmounts` WHERE `CoinAllocationID` <= 4 and `BaseCurrency` = BaseCurr and `UserID` = User_ID;

ELSE
SELECT sum(`Amount`) as `Amount` into finalAmount FROM `UserCoinAllocationAmounts` WHERE `CoinAllocationID` <= nMode and `BaseCurrency` = BaseCurr and `UserID` = User_ID;

end if;

return finalAmount;
END$$
DELIMITER ;
