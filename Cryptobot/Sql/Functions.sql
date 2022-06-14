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
