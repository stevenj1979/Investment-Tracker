DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddAvgMinPctChangeByMonth`(IN `Coin_ID` INT, IN `avgHr1_Price` DECIMAL(20,8), IN `avgHr24_Price` DECIMAL(20,8), IN `avgD7_Price` DECIMAL(20,8), IN `nMonth` INT, IN `nYear` INT)
    MODIFIES SQL DATA
BEGIN

If EXISTS (SELECT `CoinID` FROM `CoinMinPctChangeByMonth` WHERE `Month` = nMonth and `Year` = nYear and `CoinID` = Coin_ID ) Then
UPDATE `CoinMinPctChangeByMonth` SET `CoinID`= Coin_ID,`AvgHr1Price`= avgHr1_Price,`AvgHr24Price`= avgHr24_Price,`AvgD7Price`= avgD7_Price,`Year`= nYear,`Month`= nMonth WHERE `CoinID` = Coin_ID and `Year` = nYear and `Month` = nMonth;
else
INSERT INTO `CoinMinPctChangeByMonth`(`CoinID`, `AvgHr1Price`, `AvgHr24Price`, `AvgD7Price`, `Year`, `Month`) VALUES (Coin_ID, avgHr1_Price, avgHr24_Price, avgD7_Price, nYear, nMonth);
End if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddColumnToRunningCoinAmounts`(IN `nSymbol` VARCHAR(50), IN `nBaseCurrency` VARCHAR(50), IN `nAmount` DECIMAL(20,14))
    NO SQL
BEGIN
DECLARE colName TEXT;
Declare newCol Text;
select nSymbol & '-' & nBaseCurrency into newCol;
SELECT column_name INTO colName FROM information_schema.columns WHERE table_name = 'RunningCoinAmounts'
AND column_name = newCol;

IF colName is null THEN
    ALTER TABLE  `RunningCoinAmounts` ADD  newCol Dec(20,14) NOT NULL DEFAULT  0;
	INSERT INTO `RunningCoinAmounts`(newCol) VALUES (nAmount);
ELSE
	INSERT INTO `RunningCoinAmounts`(newCol) VALUES (nAmount);
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddBittrexBal`(IN `n_Sym` VARCHAR(20), IN `n_Total` DECIMAL(20,14), IN `n_Price` DECIMAL(20,14), IN `User_ID` INT)
    MODIFIES SQL DATA
begin


 INSERT INTO `BittrexBalances`(`Symbol`, `Total`, `Price`,`UserID`,`Date`) VALUES (n_Sym, n_Total, n_Price,User_ID,CURRENT_TIMESTAMP);

end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddAllTimeHigh`(IN `Coin_ID` INT, IN `High_Low` VARCHAR(40), IN `C_Price` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN

If EXISTS (SELECT `Price` FROM `AllTimeHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = High_Low ) THEN
UPDATE `AllTimeHighLow` SET `Price`= C_Price WHERE `HighLow` = High_Low and `CoinID` = Coin_ID and `Price` < C_Price;
ELSE
INSERT INTO `AllTimeHighLow`(`CoinID`, `HighLow`, `Price`) VALUES (Coin_ID, High_Low, C_Price);
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddAllTimeLow`(IN `Coin_ID` INT, IN `High_Low` VARCHAR(40), IN `C_Price` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN

If EXISTS (SELECT `Price` FROM `AllTimeHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = High_Low ) THEN
UPDATE `AllTimeHighLow` SET `Price`= C_Price WHERE `HighLow` = High_Low and `CoinID` = Coin_ID and `Price` > C_Price;
ELSE
INSERT INTO `AllTimeHighLow`(`CoinID`, `HighLow`, `Price`) VALUES (Coin_ID, High_Low, C_Price);
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddMarketPrice`(IN `Market_Price` DECIMAL(20,8), IN `Coin_ID` INT)
    MODIFIES SQL DATA
BEGIN
Declare Price_Date DateTime;

Select CURRENT_TIMESTAMP() into Price_Date;

If Not EXISTS (SELECT `PriceDate` FROM `CoinPriceChangeTime` WHERE `PriceDate` = Price_Date) THEN
INSERT INTO `CoinPriceChangeTime`(`PriceDate`) VALUES (Price_Date);
End IF;

INSERT INTO `CoinPriceChange`(`MarketPrice`, `DateTimeID`, `CoinID`) VALUES (Market_Price, (SELECT `ID` FROM `CoinPriceChangeTime` WHERE `PriceDate` = Price_Date),Coin_ID );
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddMinPctChangeByMonth`(IN `Coin_ID` INT, IN `Hr1_Price` DECIMAL(20,8), IN `Hr24_Price` DECIMAL(20,8), IN `D7_Price` DECIMAL(20,8), IN `nMonth` INT, IN `nYear` INT)
    MODIFIES SQL DATA
BEGIN

If EXISTS (SELECT `CoinID` FROM `CoinMinPctChangeByMonth` WHERE `Month` = nMonth and `Year` = nYear and `CoinID` = Coin_ID ) Then
UPDATE `CoinMinPctChangeByMonth` SET `CoinID`= Coin_ID,`Hr1Price`= Hr1_Price,`Hr24Price`= Hr24_Price,`D7Price`= D7_Price,`Year`= nYear,`Month`= nMonth WHERE `CoinID` = Coin_ID and `Year` = nYear and `Month` = nMonth;
else
INSERT INTO `CoinMinPctChangeByMonth`(`CoinID`, `Hr1Price`, `Hr24Price`, `D7Price`, `Year`, `Month`) VALUES (Coin_ID, Hr1_Price, Hr24_Price, D7_Price, nYear, nMonth);
End if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddPendingUSDtoUserProfit`(IN `user_ID` INT, IN `Price_USD` DECIMAL(14,8))
    MODIFIES SQL DATA
begin
  IF EXISTS (select * from `UserProfit` WHERE `UserID` = user_ID and Date(`ActionDate`) = Curdate()) THEN
    UPDATE `UserProfit` SET `PendingCoinsUSD`= Price_USD WHERE `UserID` = user_ID and Date(`ActionDate`) = Curdate();
 ELSE
Insert into `UserProfit` (`UserID`,`PendingCoinsUSD`, `ActionDate` ) values (user_ID,Price_USD,Curdate());
  END IF;
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddMinPriceChangeByMonth`(IN `Coin_ID` INT, IN `Hr1_Price` DECIMAL(20,8), IN `Hr24_Price` DECIMAL(20,8), IN `D7_Price` DECIMAL(20,8), IN `nMonth` INT, IN `nYear` INT)
    MODIFIES SQL DATA
BEGIN

If EXISTS (SELECT `CoinID` FROM `CoinMinPctChangeByMonth` WHERE `Month` = nMonth and `Year` = nYear and `CoinID` = Coin_ID ) Then
UPDATE `CoinMinPctChangeByMonth` SET `CoinID`= Coin_ID,`MinPrice_Hr1`= Hr1_Price,`MinPrice_Hr24`= Hr24_Price,`MinPrice_D7`= D7_Price,`Year`= nYear,`Month`= nMonth WHERE `CoinID` = Coin_ID and `Year` = nYear and `Month` = nMonth;
else
INSERT INTO `CoinMinPctChangeByMonth`(`CoinID`, `MinPrice_Hr1`, `MinPrice_Hr24`, `MinPrice_D7`, `Year`, `Month`) VALUES (Coin_ID, Hr1_Price, Hr24_Price, D7_Price, nYear, nMonth);
End if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddBuyAndSellRules`(IN `Buy_Rule` INT, IN `Sell_Rule` INT, IN `Bittrex_Ref` VARCHAR(250))
    MODIFIES SQL DATA
BEGIN
UPDATE `BittrexAction` SET `RuleIDSell` = Sell_Rule, `RuleID` = Buy_Rule WHERE `BittrexRef` = Bittrex_Ref;
UPDATE `Transaction` set `BuyRule`= Buy_Rule,
	`SellRule` = Sell_Rule,
	`FixSellRule` = Sell_Rule
WHERE `BittrexRef` = Bittrex_Ref;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddProfitForWebTable`(IN `SB_TransID` INT, IN `Orig_PP` DECIMAL(20,8), IN `Live_P` DECIMAL(20,8), IN `Sale_P` DECIMAL(20,8))
    MODIFIES SQL DATA
Begin



If EXISTS (SELECT `ID` FROM `WebSpreadBetProfits` WHERE `SpreadBetTransactionID` = SB_TransID) THEN
	UPDATE `WebSpreadBetProfits` SET `OriginalPurchasePrice`=Orig_PP,`LiveTotalPrice`=Live_P,`SaleTotalPrice`=Sale_P WHERE `SpreadBetTransactionID`= SB_TransID ;
Else
	INSERT INTO `WebSpreadBetProfits`(`SpreadBetTransactionID`, `OriginalPurchasePrice`, `LiveTotalPrice`, `SaleTotalPrice`) VALUES (SB_TransID,Orig_PP,Live_P,Sale_P);
END IF;

End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddTrackingSellCoin`(IN `Coin_Price` DECIMAL(20,14), IN `User_ID` INT, IN `Trans_ID` INT, IN `Sell_Coin` INT, IN `Send_Email` INT, IN `Offset_Enabled` INT, IN `Offset_Pct` DECIMAL(20,8), IN `Fall_InPrice` INT, IN `nType` VARCHAR(50))
    MODIFIES SQL DATA
BEGIN
DELETE FROM `TrackingSellCoins` where `TransactionID` = Trans_ID;
   		 INSERT INTO `TrackingSellCoins`(`CoinPrice`, `UserID`, `TransactionID`,`SellCoin`,`SendEmail`,`CoinSellOffsetEnabled`,`CoinSellOffsetPct`,`SellFallsInPrice`,`BaseSellPrice`,`LastPrice`,`Type`,`OriginalSellPrice`)
  		VALUES (Coin_Price,User_ID,Trans_ID,Sell_Coin,Send_Email,Offset_Enabled,Offset_Pct,Fall_InPrice,Coin_Price,Coin_Price,nType,Coin_Price);

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddToSpreadBet`(IN `SB_RuleID` INT, IN `Trans_ID` INT)
    MODIFIES SQL DATA
BEGIN

UPDATE `Transaction` SET `Type` = 'SpreadSell', `SpreadBetTransactionID` = (SELECT `ID` FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = SB_RuleID ), `SpreadBetRuleID` = SB_RuleID  where `ID` = Trans_ID;
if NOT EXISTS (SELECT  `TransactionID` FROM `SpreadBetSellTarget` WHERE `TransactionID`= Trans_ID) THEN
INSERT INTO `SpreadBetSellTarget`( `TransactionID`, `SBTransactionID`, `SellPct`)
  VALUES (Trans_ID,(SELECT `SpreadBetTransactionID` FROM `Transaction` WHERE `ID` = Trans_ID)
  ,(SELECT`PctProfitSell`/4 FROM `SpreadBetSettings` WHERE `SpreadBetRuleID` = (SELECT `SpreadBetRuleID` FROM `Transaction` WHERE `ID` = Trans_ID)) );
 END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `ChangeCoinAlertRuleID`(IN `n_name` VARCHAR(20))
    MODIFIES SQL DATA
Begin
DELETE FROM `CoinAlertsRule`;
INSERT INTO `CoinAlertsRule`( `Name`) VALUES (n_name);
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CoinSwapSell`(IN `Live_Price` DECIMAL(20,8), IN `Transaction_ID` INT, IN `Coin_ID` INT, IN `Buy_Rule` INT, IN `Buy_Amount` DECIMAL(10,2))
    MODIFIES SQL DATA
BEGIN
Declare Ord_ID Varchar(50);
Declare Coin_Swap_No, New_Transaction_ID INT;
Declare Current_Amount_USD DEC(10,2);
Declare Current_Amount DEC(20,8);
Declare Buy_Quant DEC(20,8);



Select Buy_Amount / Live_Price into Buy_Quant;

Select `NoOfCoinSwapsThisWeek` into Coin_Swap_No from `Transaction` WHERE `ID`= Transaction_ID;

Select `Amount` * `CoinPrice` into Current_Amount_USD FROM `Transaction` WHERE `ID`= Transaction_ID;

Select `Amount` into Current_Amount FROM `Transaction` WHERE `ID`= Transaction_ID;

SELECT concat('ORD',(Select `Symbol` from `Coin` where `ID` = Coin_ID),date_format(now(),"%Y%m%e%H%i%S"),Buy_Rule) into Ord_ID FROM `Transaction` WHERE `ID`= Transaction_ID;

if ((Current_Amount_USD/Buy_Amount) > 2) then
Insert into  `Transaction` (`Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `BittrexRef`, `BuyOrderCancelTime`, `SellOrderCancelTime`, `FixSellRule`, `BuyRule`, `SellRule`, `ToMerge`, `NoOfPurchases`, `NoOfCoinSwapsThisWeek`)
SELECT 'Sell', `CoinID`, `UserID`, Live_Price, Buy_Quant, 'Pending', `OrderDate`, `CompletionDate`, '', Ord_ID, '', `BuyOrderCancelTime`, `SellOrderCancelTime`, `FixSellRule`, `BuyRule`, `SellRule`, `ToMerge`, `NoOfPurchases`, Coin_Swap_No + 1 FROM `Transaction` WHERE `ID`= Transaction_ID;
Select `ID` into New_Transaction_ID FROM `Transaction` WHERE `OrderNo`= Ord_ID;
INSERT INTO `TrackingSellCoins`(`CoinPrice`,`UserID`,`TransactionID`,`Status`,`SellCoin`,`SendEmail`,`CoinSellOffsetEnabled`,`CoinSellOffsetPct`,`SellFallsInPrice`)
Select Live_Price, `UserID`, New_Transaction_ID, 'Open', 1,1,0,0,3
FROM `Transaction` WHERE `ID` = New_Transaction_ID;
update `Transaction` set `Amount` = Current_Amount - Buy_Quant,  `NoOfCoinSwapsThisWeek` = Coin_Swap_No + 1 WHERE `ID`= Transaction_ID;
update `Transaction` set `Status` = 'Pending' WHERE `ID`= New_Transaction_ID;
end if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddToPct`(IN `Coin_ID` INT, IN `User_ID` INT, IN `Pct_To_Buy` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN
UPDATE `CoinModeRules` SET `PctToBuy` = `PctToBuy` + Pct_To_Buy WHERE `CoinID` = Coin_ID and `UserID` = User_ID and `PctToBuy` <= 90.0;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CustomisedSell_RuleBased`(IN `Coin_ID` INT, IN `Buy_Rule` INT, IN `pct_of_24HrPrice` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN
Declare Live_Price DEC(20,8);
DECLARE Fixed_SellRule INT;
DECLARE Price_24Hr DEC(20,8);
DECLARE New_Sell_ID INT;

SELECT `Live24HrChange` into Price_24Hr FROM `CoinPctChange` WHERE `CoinID` = Coin_ID;
SELECT  `LiveCoinPrice` into Live_Price FROM `CoinPrice` WHERE `CoinID` = Coin_ID;
SELECT `SellRuleFixed` into Fixed_SellRule FROM `BuyRules` WHERE `ID` = Buy_Rule;

UPDATE `SellRules` SET `ProfitPctTop` = 99999.99, `ProfitPctBtm` = abs(((Live_Price - Price_24Hr)/Price_24Hr)*pct_of_24HrPrice) where `ID` = Fixed_SellRule;

Insert Into `SellRules` (`RuleName`, `UserID`, `SellCoin`, `SendEmail`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`, `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `CoinOrder`, `SellCoinOffsetEnabled`, `SellCoinOffsetPct`, `SellPriceMinEnabled`, `SellPriceMin`, `LimitToCoin`, `LimitToCoinID`, `AutoSellCoinEnabled`, `AutoSellCoinPct`, `SellPatternEnabled`, `SellPattern`, `LimitToBuyRule`, `CoinPricePatternEnabled`, `CoinPricePattern`, `CoinPriceMatchNameID`, `CoinPricePatternNameID`, `CoinPrice1HrPatternNameID`, `SellFallsInPrice`, `CoinModeRule`, `CoinSwapEnabled`, `CoinSwapAmount`, `NoOfCoinSwapsPerWeek`, `MergeCoinEnabled` )
SELECT `RuleName`, `UserID`, `SellCoin`, `SendEmail`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`, `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `CoinOrder`, `SellCoinOffsetEnabled`, `SellCoinOffsetPct`, `SellPriceMinEnabled`, `SellPriceMin`, `LimitToCoin`, `LimitToCoinID`, `AutoSellCoinEnabled`, `AutoSellCoinPct`, `SellPatternEnabled`, `SellPattern`, `LimitToBuyRule`, `CoinPricePatternEnabled`, `CoinPricePattern`, `CoinPriceMatchNameID`, `CoinPricePatternNameID`, `CoinPrice1HrPatternNameID`, `SellFallsInPrice`, `CoinModeRule`, `CoinSwapEnabled`, `CoinSwapAmount`, `NoOfCoinSwapsPerWeek`, `MergeCoinEnabled`
FROM `SellRules`
WHERE `ID` = Fixed_SellRule;

SELECT `ID` into New_Sell_ID FROM `SellRules` order by `ID` desc  Limit 1;

UPDATE `BuyRules` SET `SellRuleFixed` = New_Sell_ID where `ID` = Buy_Rule;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CustomisedSellRule`(IN `Buy_Rule` INT, IN `Coin_ID` INT)
    MODIFIES SQL DATA
begin
DECLARE p_campaign, Sell_Rule_Fixed INT(11);
DECLARE Day7Pct DECIMAL(8,4);
DECLARE c_Symbol VARCHAR(50);
select `Symbol` into c_Symbol from `Coin` where `ID` = Coin_ID;
SELECT `SellRuleFixed` into Sell_Rule_Fixed FROM `BuyRules` WHERE `ID` = Buy_Rule;
SELECT
if (`AllTimeLowPrice` = `Avg6MonthMin` and `AllTimeHighPrice` = `Avg6MonthMax`, ABS(((`LiveCoinPrice`-`Live7DChange`)/`LiveCoinPrice`)*(`PctToBuy`*(1-(`LiveCoinPrice`-`AllTimeLowPrice`)/(`AllTimeHighPrice`))*2.5)) /2, ABS(((`LiveCoinPrice`-`Live7DChange`)/`LiveCoinPrice`)*(`PctToBuy`*(1-(`LiveCoinPrice`-`AllTimeLowPrice`)/(`AllTimeHighPrice`))*2.5))  )
into Day7Pct
 FROM `CoinModePricesView` WHERE `CoinID` = Coin_ID;
If EXISTS (SELECT * FROM `SellRules` WHERE `ID` = Sell_Rule_Fixed and `CoinModeRule` <> 0) THEN
INSERT INTO `SellRules`(`UserID`, `SellCoin`, `SendEmail`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`
    , `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`, `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`
    , `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `CoinOrder`, `SellCoinOffsetEnabled`, `SellCoinOffsetPct`, `SellPriceMinEnabled`, `SellPriceMin`
    , `LimitToCoin`, `LimitToCoinID`, `AutoSellCoinEnabled`, `AutoSellCoinPct`,`SellPatternEnabled`,`SellPattern`,`CoinPricePatternEnabled`,`CoinPricePattern`,`CoinPriceMatchNameID`,`CoinPricePatternNameID`,`CoinPrice1HrPatternNameID`,`SellFallsInPrice`,`CoinModeRule`,`RuleName`)
    select `UserID`, `SellCoin`, `SendEmail`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`
    , `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`,`ProfitPctEnabled`, `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`
    , `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `CoinOrder`, `SellCoinOffsetEnabled`, `SellCoinOffsetPct`, `SellPriceMinEnabled`, `SellPriceMin`, c_Symbol, Coin_ID
    , `AutoSellCoinEnabled`, `AutoSellCoinPct`,`SellPatternEnabled`,`SellPattern`,`CoinPricePatternEnabled`,`CoinPricePattern`,`CoinPriceMatchNameID`,`CoinPricePatternNameID`,`CoinPrice1HrPatternNameID`,`SellFallsInPrice`,`CoinModeRule`,concat(c_Symbol,' Coin Mode Sell ', Sell_Rule_Fixed)
    from `SellRules`
    where `ID` = Sell_Rule_Fixed;
SELECT `ID` INTO p_campaign from `SellRules` ORDER BY `ID` DESC LIMIT 1;
UPDATE `CoinModeRules` SET `SecondarySellRules` = concat(`SecondarySellRules`,Sell_Rule_Fixed,',') where `CoinID` = Coin_ID and `RuleIDSell` = Sell_Rule_Fixed;
UPDATE `CoinModeRules` SET `RuleIDSell`= p_campaign where `CoinID` = Coin_ID and `RuleIDSell` = Sell_Rule_Fixed;
UPDATE `BuyRules` SET `SellRuleFixed` = p_campaign where `ID` = Buy_Rule;
UPDATE `SellRules` SET `ProfitPctEnabled`= 1, `ProfitPctTop` = 99.0, `ProfitPctBtm` = round(Day7Pct,4) where `ID` = Sell_Rule_Fixed;
End if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `MergeTransactions`(IN `Coin_Price` DECIMAL(16,10), IN `Trans_ID` INT, IN `In_Amount` DECIMAL(16,10))
    MODIFIES SQL DATA
Begin
UPDATE `Transaction` SET `CoinPrice`= Coin_Price,`Amount`=In_Amount,`ToMerge`= 0 WHERE `ID` = Trans_ID;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `FixCoinAmount`(IN `New_Amount` DECIMAL(20,8), IN `Trans_ID` INT)
    MODIFIES SQL DATA
BEGIN
UPDATE `Transaction` SET `CoinPrice` = (`CoinPrice`*`Amount`)/ New_Amount WHERE `ID` =  Trans_ID;

UPDATE `Transaction` SET `Amount` = New_Amount WHERE `ID` =  Trans_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `LogToSQL`(IN `User_ID` INT, IN `In_Sub` VARCHAR(100), IN `In_Comments` TEXT, IN `save_Total` INT)
    MODIFIES SQL DATA
BEGIN
  DECLARE CountOfAction INTEGER;

  SELECT Count(`ID`) INTO CountOfAction
	FROM `ActionLog`
	WHERE `UserID` = User_ID and `Subject` = In_Sub ;

  IF (CountOfAction > save_Total) THEN
    DELETE FROM `ActionLog` WHERE `UserID` = User_ID and `Subject` = In_Sub Order by `ID` limit 1;

  END IF;
INSERT INTO `ActionLog`(`UserID`, `Subject`, `Comment`) VALUES (User_ID,In_Sub,In_Comments);

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `NewUpdate1HrPriceChange`(IN `C_Price` DECIMAL(14,8), IN `Coin_ID` INT)
    MODIFIES SQL DATA
BEGIN
  DECLARE l_Price DECIMAL(14,8);
  Select `Live1HrChange` into l_price from `CoinPctChange` where `CoinID` = Coin_ID;
  If C_Price <> l_Price then
	UPDATE `CoinPctChange` SET `1HrChange5`= `1HrChange4` WHERE `CoinID` = Coin_ID;
	UPDATE `CoinPctChange` SET `1HrChange4`= `1HrChange3` WHERE `CoinID` = Coin_ID;
	UPDATE `CoinPctChange` SET `1HrChange3`= `Last1HrChange` WHERE `CoinID` = Coin_ID;
	UPDATE `CoinPctChange` SET `Last1HrChange`= `Live1HrChange` WHERE `CoinID` = Coin_ID;
	UPDATE `CoinPctChange` SET `Live1HrChange`= C_Price WHERE `CoinID` = Coin_ID;
  end if;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `NewAddBittrexSell`(IN `Coin_ID` INT, IN `Trans_ID` INT, IN `User_ID` INT, IN `n_Type` VARCHAR(50), IN `Bittrex_Ref` VARCHAR(150), IN `n_Status` VARCHAR(50), IN `Bit_Price` DECIMAL(20,8), IN `Rule_ID` INT)
    MODIFIES SQL DATA
BEGIN
Declare Og_Price DEC(20,8);
Declare Live_Price DEC(20,8);
Declare Bittrex_ID INT;

select `CoinPrice` into Og_Price from `Transaction` where `ID` = Trans_ID;

select `liveCoinPrice` into Live_Price from `CoinPrice` where `CoinID` = Coin_ID;

if NOT EXISTS (Select `TransactionID` from `BittrexAction` where `TransactionID` = Trans_ID and `Type` = n_Type) THEN
INSERT INTO `BittrexAction`(`CoinID`, `TransactionID`, `UserID`, `Type`, `BittrexRef`, `Status`, `SellPrice`, `RuleID`) VALUES (Coin_ID,Trans_ID, User_ID, n_Type, Bittrex_Ref,n_Status,Live_Price,Rule_ID);
ELSE
UPDATE `BittrexAction` SET `CoinID` = Coin_ID, `UserID` = User_ID , `Type` = n_Type, `BittrexRef` = Bittrex_Ref, `Status` = n_Status, `SellPrice` = Bit_Price, `RuleID` = Rule_ID WHERE `TransactionID` = Trans_ID and `Type` = n_Type;
end if;
 SELECT `ID` INTO Bittrex_ID FROM `BittrexAction` WHERE `BittrexRef` = Bittrex_Ref;

 UPDATE `Transaction` SET `Status` = 'Pending', `Type` = n_Type, `BittrexID` = Bittrex_ID, `BittrexRef` = Bittrex_Ref WHERE `ID` = Trans_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `OrphanedCoinModeSellRules`(IN `Sell_Rule` INT, IN `Coin_ID` INT, IN `User_ID` INT)
    MODIFIES SQL DATA
BEGIN

Declare nFlag1,nFlag2 INT;
DECLARE sell_rule_search VARCHAR(40);

select concat('%,',Sell_Rule,',%') into sell_rule_search;

if NOT EXISTS (SELECT * FROM `CoinModeRules` WHERE `RuleIDSell` = Sell_Rule and `CoinID` = Coin_ID) THEN
	select 1 into nFlag1;
end if;

if NOT EXISTS (SELECT * FROM `CoinModeRules` WHERE `SecondarySellRules` like sell_rule_search) then
	select 1 into nFlag2;
end if;

UPDATE `CoinModeRules` SET `SecondarySellRules` = concat(`SecondarySellRules`,Sell_Rule,',') WHERE `CoinID` = Coin_ID and `UserID` = UserID and nFlag1 = 1 and nFlag2 = 1;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `NewSpreadBetTransaction`(IN `User_ID` INT, IN `SpreadBet_RuleID` INT)
    MODIFIES SQL DATA
Begin

DELETE FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = SpreadBet_RuleID;

INSERT INTO `SpreadBetTransactions`(`SpreadBetRuleID`) VALUES (SpreadBet_RuleID);


end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `NewUpdateCoinPrice`(IN `Coin_ID` INT, IN `Coin_Price` DECIMAL(16,10))
    MODIFIES SQL DATA
BEGIN
declare l_price DECIMAL(16,10);
 Select `LiveCoinPrice` into l_price from `CoinPrice` where `CoinID` = Coin_ID;
 if l_price <> Coin_Price THEN
	UPDATE `CoinPrice` SET `Price5`= `Price4` where `CoinID` = Coin_ID;
	UPDATE `CoinPrice` SET `Price4`= `Price3` where `CoinID` = Coin_ID;
	UPDATE `CoinPrice` SET `Price3`= `LastCoinPrice` where `CoinID` = Coin_ID;
	UPDATE `CoinPrice` SET `LastCoinPrice` = `LiveCoinPrice` where `CoinID` = Coin_ID;
	UPDATE `CoinPrice` SET  `LiveCoinPrice` = Coin_Price where `CoinID` = Coin_ID;
	UPDATE `CoinPrice` SET `LastUpdated` = CURRENT_TIMESTAMP where `CoinID` = Coin_ID;
  end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `RefreshSpreadBetCoins`()
    MODIFIES SQL DATA
Begin
Drop Table CryptoBotHistory.SpreadBetCoins;

CREATE TABLE CryptoBotHistory.SpreadBetCoins
LIKE NewCryptoBotDb.SpreadBetCoins;

INSERT CryptoBotHistory.SpreadBetCoins
SELECT *
FROM NewCryptoBotDb.SpreadBetCoins;

End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `PriceProjectionUpdatePrice`(IN `n_Price` DECIMAL(16,8), IN `coin_ID` INT, IN `User_ID` INT, IN `low_Price` DECIMAL(16,8), IN `name_ID` INT)
    MODIFIES SQL DATA
BEGIN
	If NOT EXISTS (Select `Price` from `CoinPriceMatch` where `Price` = n_Price and `LowPrice` = low_Price and `CoinID` = coin_ID) THEN
    	Insert into `CoinPriceMatch` (`Price`,`UserID`,`LowPrice`,`CoinID`) VALUES (n_Price, User_ID, low_Price, coin_ID);
    End if;
    Delete FROM `CoinPriceMatchRules` WHERE `CoinPriceMatchNameID` = name_ID and `CoinPriceMatchID` in (SELECT `ID` FROM `CoinPriceMatch` WHERE `CoinID` = coin_ID);
    INSERT INTO `CoinPriceMatchRules` (`CoinPriceMatchID`, `CoinPriceMatchNameID`) VALUES ((select `ID` from `CoinPriceMatch` where `Price` = n_Price and `LowPrice` = low_Price and `CoinID` = coin_ID),name_ID);
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `InsertDynamicRule`(IN `Coin_ID` INT)
    MODIFIES SQL DATA
BEGIN

If NOT EXISTS (Select `ID` from `SpreadBetCoins` where `SpreadBetRuleID` = 6 and `CoinID` = Coin_ID) THEN
	INSERT INTO `SpreadBetCoins`(`SpreadBetRuleID`, `CoinID`) VALUES (6,Coin_ID);
END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `SubFromPct`(IN `Coin_ID` INT, IN `User_ID` INT, IN `Pct_To_Sub` DECIMAL(8,4), IN `Sell_Rule_ID` INT)
    MODIFIES SQL DATA
BEGIN
DECLARE Pct_To_Buy, Profit_Pct_btm DEC(8,4);

SELECT `PctToBuy` into Pct_To_Buy FROM `CoinModeRules` WHERE `CoinID` = Coin_ID and `UserID` = User_ID;
SELECT `ProfitPctBtm` into Profit_Pct_btm FROM `SellRules` WHERE `ID` = Sell_Rule_ID;

UPDATE `SellRules` SET `ProfitPctBtm` = Profit_Pct_btm/Pct_To_Buy * (Pct_To_Buy - Pct_To_Sub) WHERE `ID` = Sell_Rule_ID;
UPDATE `CoinModeRules` SET `PctToBuy` = (Pct_To_Buy - Pct_To_Sub) WHERE `CoinID` = Coin_ID;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `SaveBuyBackKitty`(IN `BB_ID` INT, IN `User_ID` INT)
    MODIFIES SQL DATA
BEGIN
Declare Trans_ID INT;
Declare Coin_ID INT;
Declare Base_Cur VARCHAR(10);
Declare BTC_Amount DEC(20,14);
Declare ETH_Amount DEC(20,14);
Declare USDT_Amount DEC(20,14);
DECLARE buy_PortionUSDT INT;
DECLARE buy_PortionBTC INT;
DECLARE buy_PortionETH INT;

SELECT `TransactionID` into Trans_ID FROM `BuyBack` WHERE `ID` = BB_ID;
SELECT `CoinID` into Coin_ID FROM `Transaction` WHERE `ID` = Trans_ID;
SELECT `BaseCurrency` into Base_Cur FROM `Coin` WHERE `ID` = Coin_ID;

if Base_Cur = 'USDT' then
	select 0 into BTC_Amount;
	select 0 into ETH_Amount;
	SELECT `Quantity`*`SellPrice` into USDT_Amount FROM `BuyBack` WHERE `ID` = BB_ID;
    select 0 into buy_PortionBTC;
    select 0 into buy_PortionETH;
    select 5 into buy_PortionUSDT;
elseif Base_Cur = 'BTC' then
	SELECT `Quantity`*`SellPrice` into BTC_Amount FROM `BuyBack` WHERE `ID` = BB_ID;
	select 0 into ETH_Amount;
	select 0 into USDT_Amount;
    select 5 into buy_PortionBTC;
    select 0 into buy_PortionETH;
    select 0 into buy_PortionUSDT;
else
	select 0 into BTC_Amount;
	SELECT `Quantity`*`SellPrice` into ETH_Amount FROM `BuyBack` WHERE `ID` = BB_ID;
	select 0 into USDT_Amount;
    select 0 into buy_PortionBTC;
    select 5 into buy_PortionETH;
    select 0 into buy_PortionUSDT;
END IF;

if EXISTS (select `UserID` from `BuyBackKitty` where `UserID` = User_ID) Then
	UPDATE `BuyBackKitty` SET `BTCAmount`= `BTCAmount` + BTC_Amount ,`USDTAmount`= `USDTAmount` + USDT_Amount
	,`ETHAmount`=`ETHAmount` + ETH_Amount,`BuyPortion`=`BuyPortion` + buy_PortionUSDT,`BuyPortionBTC`=`BuyPortionBTC` + buy_PortionBTC,`BuyPortionETH`=`BuyPortionETH` + buy_PortionETH WHERE `UserID` = User_ID AND USDT_Amount >= 0 and BTC_Amount >=0 and  ETH_Amount >= 0;
    call LogToSQL(User_ID,'BuyBackKitty','Update BTC: ' & BTC_Amount & ' USDT: ' & USDT_Amount & ' ETH: ' & ETH_Amount & ' BuyPUSDT: ' & buy_PortionUSDT & ' BuyPBTC: ' & buy_PortionBTC & ' BuyPETH: ' & buy_PortionETH,1000);
else
	INSERT INTO `BuyBackKitty`(`UserID`, `BTCAmount`, `USDTAmount`, `ETHAmount`, `BuyPortion`, `BuyPortionBTC`, `BuyPortionETH`)
Select User_ID, BTC_Amount, USDT_Amount, ETH_Amount, buy_PortionUSDT, buy_PortionBTC, buy_PortionETH From `BuyBackKitty`
where BTC_Amount >= 0 and USDT_Amount >= 0 and ETH_Amount >= 0;
    call LogToSQL(User_ID,'BuyBackKitty','Insert BTC: ' & BTC_Amount & ' USDT: ' & USDT_Amount & ' ETH: ' & ETH_Amount & ' BuyPUSDT: ' & buy_PortionUSDT & ' BuyPBTC: ' & buy_PortionBTC & ' BuyPETH: ' & buy_PortionETH,1000);
END IF;

UPDATE `BuyBack` SET `Status`= 'Closed' WHERE `ID` = BB_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `Update24HrPriceChange2`(IN `Coin_ID` INT, IN `T4_Hour_Price` DECIMAL(16,8))
    MODIFIES SQL DATA
Begin
UPDATE `CoinPctChange` SET `Last24HrChange`= `Live24HrChange` where `CoinID` = Coin_ID;
Update `CoinPctChange` SET `Live24HrChange` = T4_Hour_Price where `CoinID` = Coin_ID;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `SetNewCoinForCoinMode`(IN `User_ID` INT, IN `Coin_ID` INT)
    MODIFIES SQL DATA
BEGIN
Declare Rule_Name_Buy,Rule_Name_Sell,Coin_Name VARCHAR(50);
Declare Sell_Rule_ID, Buy_Rule_ID INT;

Select `Symbol` into Coin_Name from `Coin` where `ID` = Coin_ID;
select concat(Coin_Name, ' Coin Mode') into Rule_Name_Buy;
select concat(Coin_Name, ' Coin Mode Sell') into Rule_Name_Sell;

INSERT INTO `SellRules`(`RuleName`, `UserID`, `SellCoin`, `SendEmail`, `LimitToCoin`,`LimitToCoinID`,`BuyOrdersEnabled`,`MarketCapEnabled`,`1HrChangeEnabled`,`24HrChangeEnabled`,`7DChangeEnabled`,`ProfitPctEnabled`,`ProfitPctTop`,`ProfitPctBtm`,`CoinPriceEnabled`,`SellOrdersEnabled`,`VolumeEnabled`,`SellCoinOffsetEnabled`,`SellPriceMinEnabled`,`AutoSellCoinEnabled`,`SellPatternEnabled`,`LimitToBuyRule`,`CoinPricePatternEnabled`,`CoinPriceMatchNameID`,`CoinPricePatternNameID`,`CoinPrice1HrPatternNameID`,`SellFallsInPrice`,`CoinModeRule`,`CoinSwapEnabled`,`CoinSwapAmount`,`NoOfCoinSwapsPerWeek`)
VALUES (Rule_Name_Sell, User_ID, 0,1, Coin_Name, Coin_ID,0,0,0,0,0,1,99.9,1.0,0,0,0,0,1,0,0,'ALL',0,0,0,0,3,3,1,50.00,1);

select `ID` into Sell_Rule_ID from `SellRules` where `RuleName` = Rule_Name_Sell and `UserID` = User_ID;

INSERT INTO `BuyRules`(`RuleName`, `UserID`, `BuyOrdersEnabled`,`MarketCapEnabled`,`1HrChangeEnabled`,`24HrChangeEnabled`,`7DChangeEnabled`,`CoinPriceEnabled`,`SellOrdersEnabled`,`VolumeEnabled`,`BuyCoin`,`SendEmail`,`BuyType`           ,`BuyCoinOffsetEnabled`,`PriceTrendEnabled`,`BuyPriceMinEnabled`,`LimitToCoin`,`LimitToCoinID`,`AutoBuyCoinEnabled`,`BuyAmountOverrideEnabled`,`BuyAmountOverride`,`SellRuleFixed`
,`OverrideDailyLimit`,`CoinPricePatternEnabled`,`1HrChangeTrendEnabled`,`CoinPriceMatchID`,`CoinPricePatternID`,`Coin1HrPatternID`
,`BuyRisesInPrice`,`OverrideDisableRule`,`LimitBuyAmountEnabled`,`LimitBuyAmount`,`OverrideCancelBuyTimeEnabled`,`OverrideCancelBuyTimeMins`)
VALUES (Rule_Name_Buy, User_ID,0,0,1,0,0,0,0,0,0,1,1,0,1,1,Coin_Name,Coin_ID,0,1,50.00,Sell_Rule_ID, 1, 1, 0, 1, 1, 0, 2, 1, 1, 60.00, 1, 30);

select `ID` into Buy_Rule_ID from `BuyRules` where `RuleName` = Rule_Name_Buy and `UserID` = User_ID and `LimitToCoinID` = Coin_ID;

If NOT EXISTS (SELECT * FROM `CoinModeRules`  WHERE `CoinID` = Coin_ID ) THEN
	INSERT INTO `CoinModeRules`(`CoinID`, `RuleID`, `ModeID`, `RuleIDSell`, `USDBuyAmount`, `Hr1Top`, `Hr1Btm`, `Hr24Top`, `Hr24Btm`, `D7Top`, `D7Btm`, `SecondarySellRules`, `UserID`, `CoinModeBuyRuleEnabled`, `CoinModeSellRuleEnabled`)
SELECT Coin_ID, Buy_Rule_ID, `ModeID`, Sell_Rule_ID, `USDBuyAmount`, `Hr1Top`, `Hr1Btm`, `Hr24Top`, `Hr24Btm`, `D7Top`, `D7Btm`, `SecondarySellRules`, User_ID, `CoinModeBuyRuleEnabled`, `CoinModeSellRuleEnabled` FROM `CoinModeRules` where `UserID` = User_ID Limit 1;
else
	UPDATE `CoinModeRules` SET `RuleID` = Buy_Rule_ID , `RuleIDSell` =  Sell_Rule_ID  WHERE `CoinID` = Coin_ID and `UserID` = User_ID;
End if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `Update1HrPriceChange2`(IN `Coin_ID` INT, IN `One_Hour_Price` DECIMAL(20,12))
    MODIFIES SQL DATA
Begin
UPDATE `CoinPctChange` SET `Last1HrChange`= `Live1HrChange` where `CoinID` = Coin_ID;
Update `CoinPctChange` SET `Live1HrChange` = One_Hour_Price where `CoinID` = Coin_ID;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `Update7DPriceChange`(IN `Coin_ID` INT, IN `Live_7D_Price` DECIMAL(16,8))
    MODIFIES SQL DATA
Begin
UPDATE `CoinPctChange` SET `Last7DChange`= `Live7DChange` where `CoinID` = Coin_ID;
Update `CoinPctChange` SET `Live7DChange` = Live_7D_Price where `CoinID` = Coin_ID;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `ResidualCoinToSaving`(IN `new_Amount` DECIMAL(20,14), IN `Order_ID` VARCHAR(100), IN `Trans_ID` INT,IN `old_Amount` DECIMAL(20,14))
    MODIFIES SQL DATA
BEGIN
Declare to_Merge Int;
Set to_Merge = 0;
Select `AutoMergeSavings` into to_Merge from `UserConfig` where `UserID` = (Select `UserID` From `Transaction` where `ID` = Trans_ID );

INSERT INTO `Transaction`( `Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `BittrexRef`, `BuyOrderCancelTime`, `SellOrderCancelTime`, `FixSellRule`, `BuyRule`, `SellRule`, `ToMerge`, `NoOfPurchases`, `NoOfCoinSwapsThisWeek`, `NoOfCoinSwapPriceOverrides`, `SpreadBetTransactionID`, `CaptureTrend`, `SpreadBetRuleID`, `OriginalAmount`)
SELECT `Type`, `CoinID`, `UserID`, `CoinPrice`, new_Amount, 'Saving', now(), `CompletionDate`, 0, Order_ID, '', `BuyOrderCancelTime`, `SellOrderCancelTime`, `FixSellRule`, `BuyRule`, `SellRule`, to_Merge, `NoOfPurchases`, `NoOfCoinSwapsThisWeek`, `NoOfCoinSwapPriceOverrides`, (SELECT `ID` FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = 10), `CaptureTrend`, 10, old_Amount FROM `Transaction` WHERE `ID` = Trans_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateBuyTrend`(IN `Coin_ID` INT, IN `Transaction_ID` INT, IN `n_Mode` VARCHAR(50), IN `n_ID` INT, IN `Hr_1Pct` DECIMAL(20,8), IN `Hr_24Pct` DECIMAL(20,8), IN `D_7Pct` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN
if n_Mode = 'CoinMode' then
	INSERT INTO `CoinBuyTrends`(`CoinID`, `ModeID`, `Hr1Pct`, `Hr24Pct`, `D7Pct`, `TransactionID`)
    	VALUES (Coin_ID,n_ID,Hr_1Pct,Hr_24Pct,D_7Pct ,Transaction_ID);
	UPDATE `Transaction` SET `CaptureTrend`= 1 WHERE `ID` = Transaction_ID;
ELSEIF  n_Mode = 'SpreadBet' then
	INSERT INTO `CoinBuyTrends`(`CoinID`, `SpreadID`, `Hr1Pct`, `Hr24Pct`, `D7Pct`, `TransactionID`)
    	VALUES (Coin_ID,n_ID,Hr_1Pct,Hr_24Pct,D_7Pct ,Transaction_ID);
	UPDATE `Transaction` SET `CaptureTrend` = 1 WHERE `SpreadBetTransactionID` =
    	(SELECT `ID` FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = n_ID) ;
ELSE
	INSERT INTO `CoinBuyTrends`(`CoinID`, `RuleID`, `Hr1Pct`, `Hr24Pct`, `D7Pct`, `TransactionID`)
    	VALUES (Coin_ID,n_ID,Hr_1Pct,Hr_24Pct,D_7Pct ,Transaction_ID);
	UPDATE `Transaction` SET `CaptureTrend`= 1 WHERE `ID` = Transaction_ID;
end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `SubPctFromCoinSwap`(IN `coin_SwapID` INT)
    NO SQL
BEGIN
DECLARE temp_Pct DEC(20,14);
DECLARE temp_Multiply INT;

Select `PctToBuy` into temp_Pct FROM `SwapCoins` where `ID` = coin_SwapID;
SET temp_Multiply = Floor(ABS(temp_Pct/3))+1;

UPDATE `SwapCoins` SET `PctToBuy` = (`PctToBuy`-(0.24 * temp_Multiply)) where `ID` = coin_SwapID and `PctToBuy` >= 4.0;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateCMCStatstoSQL`(IN `Coin_ID` INT, IN `Market_Cap` DECIMAL(14,2), IN `Pct_Change_1Hr` DECIMAL(6,2), IN `Pct_Change_24Hr` DECIMAL(6,2), IN `Pct_Change_7d` DECIMAL(6,2))
    MODIFIES SQL DATA
BEGIN
UPDATE `CoinMarketCap` SET `LastMarketCap`=`LiveMarketCap`WHERE `CoinID` =  Coin_ID;
UPDATE `CoinMarketCap` SET `LiveMarketCap` = Market_Cap WHERE `CoinID` =  Coin_ID;

UPDATE `CoinPctChange` SET `1HrChange5`= `1HrChange4` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `1HrChange4`= `1HrChange3` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `1HrChange3`= `Last1HrChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Last1HrChange`= `Live1HrChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live1HrChange`= Pct_Change_1Hr WHERE `CoinID` = Coin_ID;

UPDATE `CoinPctChange` SET `Last24HrChange` = `Live24HrChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live24HrChange` = Pct_Change_24Hr WHERE `CoinID` = Coin_ID;

UPDATE `CoinPctChange` SET `Last7DChange` = `Live7DChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live7DChange` = Pct_Change_7d WHERE `CoinID` = Coin_ID;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateCoinModeRules`(IN `Coin_ID` INT, IN `n_ID` INT, IN `n_Mode` VARCHAR(50))
    MODIFIES SQL DATA
BEGIN

UPDATE `CoinModeRules` SET `Hr1Btm` = Get1HrPct(Coin_ID,n_ID,n_Mode), `Hr1Top` = (Get1HrPct(Coin_ID,n_ID,n_Mode) + 0.8), `Hr24Btm` = -99.9, `Hr24Top` = Get24HrPct(Coin_ID,n_ID,n_Mode), `D7Btm` = -99.9, `D7Top` = Get7DPct(Coin_ID,n_ID,n_Mode) WHERE `CoinID` = Coin_ID and `RuleID` = n_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateBittrexStatstoSQL`(IN `Coin_ID` INT, IN `Coin_Vol` DECIMAL(14,8), IN `Coin_Sell` DECIMAL(14,8), IN `Coin_Buy` DECIMAL(14,8))
    NO SQL
BEGIN
UPDATE `CoinVolume` SET `LastVolume`= `LiveVolume`WHERE `CoinID` = Coin_ID;
UPDATE `CoinVolume` SET `LiveVolume` = Coin_Vol WHERE `CoinID` = Coin_ID;

UPDATE `CoinSellOrders` SET `LastSellOrders`= `LiveSellOrders` WHERE `CoinID` = Coin_ID;
UPDATE `CoinSellOrders` SET `LiveSellOrders` = Coin_Sell WHERE `CoinID` = Coin_ID;

UPDATE `CoinBuyOrders` SET `LastBuyOrders`= `LiveBuyOrders` WHERE `CoinID` = Coin_ID;
UPDATE `CoinBuyOrders` SET `LiveBuyOrders` = Coin_Buy WHERE `CoinID` = Coin_ID;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateMonthlyMinMaxPrice`(IN `Coin_ID` INT, IN `Min_Price` DECIMAL(20,8), IN `Max_Price` DECIMAL(20,8), IN `n_Month` INT, IN `n_Year` INT)
    MODIFIES SQL DATA
BEGIN
 IF EXISTS (SELECT `ID` FROM `MonthlyMaxPrices` WHERE `CoinID` = Coin_ID and `Month` = n_Month and `Year` = n_Year ) then
	UPDATE `MonthlyMaxPrices` SET `MaxPrice`= Max_Price WHERE `CoinID` = Coin_ID and `Month` = n_Month and `Year` = n_Year ;
Else
	INSERT INTO `MonthlyMaxPrices`(`CoinID`, `MaxPrice`, `Month`, `Year`) VALUES (Coin_ID,Max_Price,n_Month,n_Year);
End if;
If EXISTS ( SELECT  `ID` FROM `MonthlyMinPrices` WHERE `CoinID` = Coin_ID and `Month` = n_Month and `Year` = n_Year ) then
	UPDATE `MonthlyMinPrices` SET `MinPrice`= Min_Price WHERE `CoinID` = Coin_ID and `Month` = n_Month and `Year` = n_Year;
Else
	INSERT INTO `MonthlyMinPrices`(`CoinID`, `MinPrice`, `Month`, `Year`) VALUES (Coin_ID,Min_Price,n_Month,n_Year);
End if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateCoinPctChange`(IN `coin_ID` INT, IN `D7_Price` DECIMAL(20,8), IN `H24_Price` DECIMAL(20,8), IN `H1_Price` DECIMAL(20,8))
    MODIFIES SQL DATA
Begin
Update `CoinPctChange` SET `Last7DChange` =`Live7DChange` where `CoinID` = coin_ID;
Update `CoinPctChange` SET `Live7DChange` = D7_Price where `CoinID` = coin_ID;
Update `CoinPctChange` SET `Last24HrChange` =`Live24HrChange` where `CoinID` = coin_ID;
Update `CoinPctChange` SET `Live24HrChange` = H24_Price where `CoinID` = coin_ID;
Update `CoinPctChange` SET `Last24HrChange` =`Live1HrChange` where `CoinID` = coin_ID;
Update `CoinPctChange` SET `Live1HrChange` = H1_Price where `CoinID` = coin_ID;
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateNewUserProfit`()
    MODIFIES SQL DATA
BEGIN
DELETE FROM `NewUserProfit`;
Insert into `NewUserProfit`
    SELECT `Tv`.`CoinID`,`Tv`.`CoinPrice`,`Tv`.`Amount`,`Tv`.`Status`,`Tv`.`UserID`, `Cp`.`LiveCoinPrice`
  , `Tv`.`CoinPrice`*`Tv`.`Amount`as PurchasePrice
  ,`Cp`.`LiveCoinPrice` *`Tv`.`Amount`as LivePrice
  ,(`Cp`.`LiveCoinPrice` *`Tv`.`Amount`) - (`Tv`.`CoinPrice`*`Tv`.`Amount`)  as Profit
  ,`Tv`.`BuyRule` as `RuleID`
,`Tv`.`SpreadBetRuleID`
,`Tv`.`SpreadBetTransactionID`
  FROM `TransactionsView` `Tv`
  join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tv`.`CoinID`
  WHERE (`Tv`.`SpreadBetTransactionID` in (SELECT `Tv`.`SpreadBetTransactionID`
  FROM `TransactionsView` `Tv`
  WHERE (`Tv`.`Status` = 'Open') OR (`Tv`.`Status` = 'Pending')));
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateRulesforCoinMode`(IN `Coin_ID` INT, IN `Rule_ID` INT, IN `High_Price` DECIMAL(20,8), IN `Low_Price` DECIMAL(20,8), IN `Buy_Amount` DECIMAL(20,8), IN `n_enable` INT, IN `n_type` INT, IN `SellRule_ID` INT, IN `Rises_Falls` INT, IN `MinsToCancel` INT, IN `Hr1_Top` DECIMAL(20,8), IN `Sell_Rule_Enabled` BOOLEAN, IN `Coin_Mode_Override` INT, IN `Coin_Price_Pattern_Enabled` INT)
    MODIFIES SQL DATA
BEGIN
DECLARE Coin_Mode_Sell_Enabled INT;
Declare Hr1_BtmLookup DEC(20,8);
SET Hr1_BtmLookup = Get1HrPct(Coin_ID,Rule_ID,'CoinMode');
SELECT `CoinModeSellRuleEnabled` into Coin_Mode_Sell_Enabled FROM `CoinModeRules` WHERE `RuleIDSell` = SellRule_ID;
    if (n_type = 1) THEN
		UPDATE `BuyRules` SET `LimitToCoin` = (Select `Symbol` from `Coin` where `ID` = Coin_ID), `LimitToCoinID`= Coin_ID, `SellRuleFixed`= SellRule_ID, `OverrideDailyLimit`=1, `BuyCoin` = n_enable,`BuyPriceMinEnabled` = 0,`BuyPriceMin` = High_Price,`AutoBuyCoinEnabled` = 1,`BuyAmountOverrideEnabled` = 1,`BuyAmountOverride` = Buy_Amount, `DisableUntil` = date_sub(now(),Interval 24 Hour), `SendEmail` = 1, `AutoBuyCoinEnabled` = 1, `CoinPriceMatchID` = 0, `CoinPricePatternID` = 1,`1HrChangeTop` = (Hr1_BtmLookup + 0.8), `1HrChangeBtm` = Hr1_BtmLookup, `BuyRisesInPrice` = Rises_Falls, `OverrideCancelBuyTimeEnabled` = 1, `OverrideCancelBuyTimeMins` = MinsToCancel, `CoinPricePatternEnabled` = 0 , `PriceTrendEnabled` = Coin_Price_Pattern_Enabled, `CoinPriceMatchID` = 1, `CoinMode` = n_type where `ID`= Rule_ID;
		UPDATE `CoinModeRules` SET `ModeID` = n_type WHERE `RuleID` = Rule_ID and `CoinID` = Coin_ID;
		UPDATE `SellRules` SET `SellCoin`= Sell_Rule_Enabled WHERE  `ID` = SellRule_ID;
    ELSEIF (n_type = 2) THEN
    	UPDATE `SellRules` set `SellCoin` = n_enable, `ProfitPctEnabled` = 1, `CoinPriceEnabled` = 0, `CoinPriceTop` = High_Price, `CoinPriceBtm` = Low_Price,`SellPriceMinEnabled` = 0, `SellPriceMin` = Low_Price, `LimitToCoin` = (Select `Symbol` from `Coin` where `ID` = Coin_ID), `LimitToCoinID`= Coin_ID, `SellFallsInPrice`= 3, `SellFallsInPrice` = Rises_Falls, `SellPatternEnabled` = Coin_Price_Pattern_Enabled WHERE `ID` = SellRule_ID;
		UPDATE `CoinModeRules` SET `ModeID` = n_type WHERE `RuleID` = Rule_ID and `CoinID` = Coin_ID;
		UPDATE `BuyRules` SET `BuyCoin`= 0 where `ID` = Rule_ID;
    ELSE
    	UPDATE `SellRules` set `SellCoin` = Sell_Rule_Enabled  WHERE `ID` = SellRule_ID and Coin_Mode_Sell_Enabled = 1;
		UPDATE `CoinModeRules` SET `ModeID` = n_type WHERE `RuleID` = Rule_ID and `CoinID` = Coin_ID;
        UPDATE `BuyRules` SET `BuyCoin`= 0, `CoinModeOverridePriceEnabled` = 1 where `ID` = Rule_ID;
    end IF;
    if (Coin_Mode_Override = 1) THEN
    	Update `BuyRules` SET `CoinModeOverridePriceEnabled` = 1 where `ID` = Rule_ID;
    else
    Update `BuyRules` SET `CoinModeOverridePriceEnabled` = 0 where `ID` = Rule_ID;
    end if;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateLastPriceBearBull`(IN `Coin_ID` INT, IN `nPrice` DECIMAL(20,8))
    MODIFIES SQL DATA
Begin
IF EXISTS (SELECT `ID` FROM `BearBullStats` WHERE `CoinID` = Coin_ID ) THEN
	UPDATE `BearBullStats` SET `LastPriceChange`= nPrice WHERE `CoinID` = Coin_ID;
ELSE
	INSERT INTO `BearBullStats`(`CoinID`, `LastPriceChange`) VALUES (Coin_ID,nPrice);
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateSellTrendToSQL`(IN `p_mode` VARCHAR(50), IN `rule_ID` INT, IN `Coin_ID` INT, IN `n_Price` DECIMAL(20,8))
    MODIFIES SQL DATA
Begin

If p_mode = 'SpreadBetRuleID' THEN
If Exists (SELECT `ID` FROM `CoinSellTrends` WHERE `CoinID` = Coin_ID and `SpreadBetRuleID` = rule_ID) Then
	UPDATE `CoinSellTrends` SET `AvgSellPct`=((`AvgSellPct` * `NoOfTransactions`) + n_Price)/(`NoOfTransactions`+1),`NoOfTransactions`=`NoOfTransactions`+1 WHERE `CoinID` = Coin_ID and `SpreadBetRuleID` = rule_ID;
Else
	INSERT INTO `CoinSellTrends`(`CoinID`, `AvgSellPct`, `NoOfTransactions`, `SpreadBetRuleID`) VALUES (Coin_ID, n_Price, 1, rule_ID);
End If;
ELSEIF p_mode = 'RuleID' Then
If Exists (SELECT `ID` FROM `CoinSellTrends` WHERE `CoinID` = Coin_ID and `RuleID` = rule_ID) Then
	UPDATE `CoinSellTrends` SET `AvgSellPct`=((`AvgSellPct` * `NoOfTransactions`) + n_Price)/(`NoOfTransactions`+1),`NoOfTransactions`=`NoOfTransactions`+1 WHERE `CoinID` = Coin_ID and `RuleID` = rule_ID;
Else
	INSERT INTO `CoinSellTrends`(`CoinID`, `AvgSellPct`, `NoOfTransactions`, `RuleID`) VALUES (Coin_ID, n_Price, 1, rule_ID);
End If;
ELSEIF p_mode = 'ModeID' THEN
If Exists (SELECT `ID` FROM `CoinSellTrends` WHERE `CoinID` = Coin_ID and `ModeID` = rule_ID) Then
	UPDATE `CoinSellTrends` SET `AvgSellPct`=((`AvgSellPct` * `NoOfTransactions`) + n_Price)/(`NoOfTransactions`+1),`NoOfTransactions`=`NoOfTransactions`+1 WHERE `CoinID` = Coin_ID and `ModeID` = rule_ID;
Else
	INSERT INTO `CoinSellTrends`(`CoinID`, `AvgSellPct`, `NoOfTransactions`, `ModeID`) VALUES (Coin_ID, n_Price, 1, rule_ID);
End If;
End if;



End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateSpreadBetRules`(IN `Coin_ID` INT, IN `n_ID` INT, IN `n_Mode` VARCHAR(50))
    MODIFIES SQL DATA
BEGIN
DECLARE Hr1_PctLookup DEC(20,8);
DECLARE Hr24_PctLookup DEC(20,8);
DECLARE D7_PctLookup DEC(20,8);

SET Hr1_PctLookup = Get1HrPct(Coin_ID,n_ID,n_Mode);
SET Hr24_PctLookup = Get24HrPct(Coin_ID,n_ID,n_Mode);
SET D7_PctLookup = Get7DPct(Coin_ID,n_ID,n_Mode);

UPDATE `SpreadBetSettings` SET `Hr1BuyPrice` = Hr1_PctLookup, `Hr24BuyPrice` = Hr24_PctLookup, `D7BuyPrice` = D7_PctLookup  WHERE `SpreadBetRuleID` = n_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateOrAddSBTransSellTargetPct`(IN `Trans_ID` INT, IN `sub_Amount` DECIMAL(20,8), IN `SBTrans_ID` INT)
    MODIFIES SQL DATA
BEGIN
DECLARE temp_sellPct DEC(20,8);
DECLARE temp_SBRuleID INT;
DECLARE temp_PctProfit DEC(20,8);
DECLARE temp_Multiply INT;

SELECT `SpreadBetRuleID` INTO temp_SBRuleID FROM `Transaction` WHERE `ID` = Trans_ID;
Select `PctProfitSell` INTO temp_sellPct FROM `SpreadBetSettings` WHERE `SpreadBetRuleID` = temp_SBRuleID;


If EXISTS (SELECT `TransactionID` FROM `SpreadBetSellTarget` WHERE  `TransactionID` = Trans_ID) THEN
	SELECT `SellPct` into temp_PctProfit FROM `SpreadBetSellTarget` WHERE `TransactionID` = Trans_ID;
	SET temp_Multiply = FLOOR(ABS(temp_PctProfit/1))+1;
	UPDATE `SpreadBetSellTarget` SET `SellPct`= (`SellPct`- (sub_Amount * temp_Multiply)) WHERE `TransactionID` = Trans_ID and `SellPct` > 1.5;
else
	INSERT INTO `SpreadBetSellTarget`(`TransactionID`, `SBTransactionID`, `SellPct`) VALUES (Trans_ID,SBTrans_ID, temp_sellPct);
end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateTransCount`(IN `nCount` INT, IN `i_d` INT)
    MODIFIES SQL DATA
BEGIN
DECLARE l_Price INT;
SELECT  `NoOfPurchases` INTO  l_Price  FROM `Transaction` WHERE `ID` = i_d;
UPDATE `Transaction` SET `NoOfPurchases`=  l_Price + nCount WHERE `ID` = i_d;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateTransToSpread`(IN `Spred_RuleID` INT, IN `Coin_ID` INT, IN `User_ID` INT, IN `Spred_TransID` INT)
    MODIFIES SQL DATA
BEGIN
Declare Trans_ID INT;

select `ID` into Trans_ID FROM `Transaction` where `CoinID` = Coin_ID and `UserID` = User_ID and `Status` in ('Pending','Open') order by `ID` Desc Limit 1;

UPDATE `Transaction` SET `Type` = 'SpreadSell', `SpreadBetTransactionID` = Spred_TransID, `SpreadBetRuleID` = Spred_RuleID WHERE `ID` = Trans_ID;

UPDATE `BittrexAction` SET `Type` = 'SpreadSell' where `TransactionID` = Trans_ID ;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateUSDTBal`(IN `n_Sym` VARCHAR(50), IN `n_usdtPurchase` DECIMAL(20,8), IN `User_ID` INT, IN `n_Price` DECIMAL(20,8))
    MODIFIES SQL DATA
begin

  IF EXISTS (SELECT `ID` FROM `BittrexBalances` WHERE `Symbol` = n_Sym and day(`Date`) = day(now()) and month(`Date`) = month(now()) and year(`Date`) = year(now()) ) THEN
   	UPDATE `BittrexBalances` SET `Total` = `Total` + n_usdtPurchase, `Price` = n_Price WHERE `UserID` = User_ID and `Symbol` = n_Sym and day(`Date`) = day(now()) and month(`Date`) = month(now()) and year(`Date`) = year(now()) ;
 ELSE
	INSERT INTO `BittrexBalances`(`Symbol`, `Total`, `Price`,`UserID`)
    select `Symbol`, `Total` + n_usdtPurchase, `Price`,`UserID` from `BittrexBalances` where `UserID` = User_ID and `Symbol` = n_Sym order by `Date` DESC limit 1 ;
  END IF;
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateSplitAmountForRule`(IN `User_ID` INT)
    MODIFIES SQL DATA
BEGIN
Declare Split_Enabled TinyInt;
Declare n_splits INT;
Declare n_allocation DEC(20,8);

SELECT `SplitBuyAmounByPctEnabled` into Split_Enabled FROM `UserConfig` WHERE `UserID` = User_ID;
SELECT `NoOfSplits` into n_splits FROM `UserConfig` WHERE `UserID` = User_ID;
SELECT `RuleBasedOpenUSDT`+`RuleBasedAvailable` into n_allocation FROM `CoinAmountsAvailableToBuy` WHERE `ID` = User_ID;

update `BuyRules` SET `BuyAmountOverride` = CASE
        WHEN Split_Enabled = 1 THEN n_allocation/n_splits
        ELSE `BuyAmountOverride`
        end
 WHERE `BuyCoin` = 1 and `UserID` = User_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `UpdateSpreadBetTotalProfit`()
    MODIFIES SQL DATA
Begin
Delete From `SpreadBetTotalProfit`;
INSERT INTO `SpreadBetTotalProfit`(`TransactionID`, `SpreadBetTransactionID`, `CoinPrice`, `SalePrice`, `Amount`, `Status`,`OriginalAmount`)
SELECT
`Tv`.`ID` as TransactionID
,`Tv`.`SpreadBetTransactionID`
,`Tv`.`CoinPrice`
,`Ba`.`SellPrice` as SalePrice
,`Tv`.`Amount`
,`Tv`.`Status`
,`Tv`.`OriginalAmount`
  FROM `Transaction` `Tv`
  join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tv`.`CoinID`
  Left join `BittrexAction` `Ba` on `Ba`.`TransactionID` = `Tv`.`ID` and `Ba`.`Type` in ('Sell','SpreadSell')
  where `Tv`.`SpreadBetTransactionID` in (SELECT `Tr`.`SpreadBetTransactionID`
  FROM `Transaction` `Tr`
  WHERE (`Tr`.`Status` = 'Open') OR (`Tr`.`Status` = 'Pending'))
  and `Tv`.`Status` <> 'Closed';

End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `WritePriceProjection`(IN `Coin_ID` INT, IN `Max_Price_Live` DECIMAL(20,8), IN `Min_Price_Live` DECIMAL(20,8), IN `Min15_Max` DECIMAL(20,8), IN `Min15_Min` DECIMAL(20,8), IN `Min30_Max` DECIMAL(20,8), IN `Min30_Min` DECIMAL(20,8), IN `Min45_Max` DECIMAL(20,8), IN `Min45_Min` DECIMAL(20,8), IN `Min60_Max` DECIMAL(20,8), IN `Min60_Min` DECIMAL(20,8), IN `Min75_Max` DECIMAL(20,8), IN `Min75_Min` DECIMAL(20,8))
    MODIFIES SQL DATA
Begin
    UPDATE `ProjectedPriceMax` SET `0Min` = Max_Price_Live WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMax` SET `15Min` = Min15_Max WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMax` SET `30Min` = Min30_Max WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMax` SET `45Min` = Min45_Max WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMax` SET `60Min` = Min60_Max WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMax` SET `75Min` = Min75_Max WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMin` SET `0Min` = Min_Price_Live WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMin` SET `15Min` = Min15_Min WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMin` SET `30Min` = Min30_Min WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMin` SET `45Min` = Min45_Min WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMin` SET `60Min` = Min60_Min WHERE `CoinID` = Coin_ID;
    UPDATE `ProjectedPriceMin` SET `75Min` = Min75_Min WHERE `CoinID` = Coin_ID;
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `WritePricePctIncrease`(IN `Coin_ID` INT, IN `Price_1` DECIMAL(20,8), IN `Price_2` DECIMAL(20,8), IN `Price_3` DECIMAL(20,8), IN `Price_4` DECIMAL(20,8), IN `Price_5` DECIMAL(20,8), IN `Price_6` DECIMAL(20,8), IN `n_date` DATETIME)
    NO SQL
BEGIN
 if ((Select Count(`ID`) FROM `CoinPricePctIncrease`)> 5000) THEN
 	delete from `CoinPricePctIncrease` where `ID` = (Select * from `CoinPricePctIncrease` order by `ID` asc limit 1);
 end if;
INSERT INTO `CoinPricePctIncrease`(`CoinID`, `OneHourPct`, `TwentyFourHourPct`, `SevenDayPct`, `PriceDate`, `TenMinsPct`, `TwentyMinsPct`, `ThirtyMinsPct`)
  VALUES (Coin_ID,Price_1,Price_2,Price_3,n_date,Price_4,Price_5,Price_6);
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `WriteBuyBack`(IN `Trans_ID` INT, IN `Rises_InPrice` INT, IN `Profit_PCT` DECIMAL(20,8), IN `Mins_ToCancel` INT, IN `Final_Price` DECIMAL(20,8), IN `nAmount` DECIMAL(20,8), IN `nCost` DECIMAL(20,8), IN `USD_Amount` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN
DECLARE BuyBack_TransID INT;
DECLARE newRuleID INT;
DECLARE buy_ruleID INT;
Declare Buy_Amount_override_Enabled INT;
DECLARE Buy_Amount DEC(20,14);
DECLARE User_ID INT;
DECLARE Base_Curr Varchar(50);
DECLARE final_Price_Multiplier DEC(20,14);
Declare Coin_ID INT;

SELECT `CoinID` into Coin_ID From `Transaction` WHERE `ID` = Trans_ID;
SELECT `BaseCurrency` into Base_Curr FROM `Coin` WHERE `ID` = Coin_ID;

if (Base_Curr = 'USDT') THEN
	SELECT getBTCPrice(83) into final_Price_Multiplier;
elseif (Base_Curr = 'BTC') THEN
	SELECT getBTCPrice(84) into final_Price_Multiplier;
else
	SELECT getBTCPrice(85) into final_Price_Multiplier;
end if;



SELECT `BuyRule` into buy_ruleID FROM `Transaction` WHERE `ID` = Trans_ID;

SELECT `BuyAmountOverrideEnabled` into Buy_Amount_override_Enabled FROM `BuyRules` WHERE `ID` = buy_ruleID;

SELECT `UserID` into User_ID FROM `Transaction` WHERE `ID` = Trans_ID;

if (Buy_Amount_override_Enabled = 1) THEN
	SELECT `BuyAmountOverride`/final_Price_Multiplier into Buy_Amount FROM `BuyRules` WHERE `ID` = buy_ruleID;
else
	SELECT `BTCBuyAmount`/final_Price_Multiplier into Buy_Amount FROM `UserConfig` WHERE `UserID` = User_ID;
End if;

SELECT `BuyBackTransactionID` into BuyBack_TransID FROM `Transaction` Where `ID` = Trans_ID;

If BuyBack_TransID = 0 THEN
	INSERT into `BuyBackTransaction` (`Name`) VALUES (concat('BuyBack_' , Trans_ID));
	Select `ID` into newRuleID FROM `BuyBackTransaction` WHERE `Name` = concat('BuyBack_' , Trans_ID);
	UPDATE `Transaction` SET `BuyBackTransactionID` = newRuleID WHERE `ID` = Trans_ID;
END if;

If EXISTS (SELECT `TransactionID` FROM `BuyBack` WHERE `TransactionID` = Trans_ID) THEN
UPDATE `BuyBack` SET `Quantity`= nAmount,`Status`= 'Open',`NoOfRaisesInPrice`= Rises_InPrice,`BuyBackPct`= -ABS(Profit_PCT),`MinsToCancel`= Mins_ToCancel,`SellPrice` = Final_Price, `CoinPrice` = nCost,  `USDBuyBackAmount` = Buy_Amount WHERE `TransactionID` = Trans_ID;

else
INSERT INTO `BuyBack`(`TransactionID`, `Quantity`, `Status`,`NoOfRaisesInPrice`,`BuyBackPct`,`MinsToCancel`,`SellPrice`,`CoinPrice`,`USDBuyBackAmount`)
  VALUES (Trans_ID, nAmount, 'Open',Rises_InPrice, -ABS(Profit_PCT),Mins_ToCancel, Final_Price, nCost, Buy_Amount);

end if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `addCMCData`(IN `CMC_ID` INT, IN `Hr_1Price` DECIMAL(20,8), IN `Hr_24Price` DECIMAL(20,8), IN `Day_7Price` DECIMAL(20,8), IN `Day_30Price` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN
Declare Coin_ID INT;
SELECT `ID` into Coin_ID FROM `Coin` WHERE `CMCID` = CMC_ID and `BuyCoin` = 1;

IF EXISTS(SELECT `ID` FROM `CMCData` WHERE `CoinID` = Coin_ID) THEN

UPDATE `CMCData` SET `1HrPrice` = Hr_1Price,`24HrPrice`= Hr_24Price,`7DayPrice`= Day_7Price,`30DayPrice`= Day_30Price WHERE `CoinID` = Coin_ID and `CMCID` = CMC_ID;

else

INSERT INTO `CMCData`(`CoinID`, `CMCID`, `1HrPrice`, `24HrPrice`, `7DayPrice`, `30DayPrice`) VALUES (Coin_ID,CMC_ID,Hr_1Price,Hr_24Price,Day_7Price,Day_30Price);
end if;

end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `addCoinPurchaseDelay`(IN `Coin_ID` INT, IN `User_ID` INT, IN `delay_Min` INT, IN `Days_Enabled` INT)
    MODIFIES SQL DATA
BEGIN
If Days_Enabled = 1 Then
  If EXISTS (SELECT `ID` FROM `DelayCoinPurchase` WHERE `CoinID` = Coin_ID and `UserID` = User_ID) THEN
  	UPDATE `DelayCoinPurchase` SET `DelayTime`= date_add(now(), Interval delay_Min DAY) WHERE `CoinID`= Coin_ID and `UserID`=User_ID;
  else
  	INSERT INTO `DelayCoinPurchase`(`CoinID`, `UserID`, `DelayTime`) VALUES (Coin_ID, User_ID, date_add(now(), Interval delay_Min DAY));
  end if;
else
  If EXISTS (SELECT `ID` FROM `DelayCoinPurchase` WHERE `CoinID` = Coin_ID and `UserID` = User_ID) THEN
    UPDATE `DelayCoinPurchase` SET `DelayTime`= date_add(now(), Interval delay_Min MINUTE) WHERE `CoinID`= Coin_ID and `UserID`=User_ID;
  else
    INSERT INTO `DelayCoinPurchase`(`CoinID`, `UserID`, `DelayTime`) VALUES (Coin_ID, User_ID, date_add(now(), Interval delay_Min MINUTE));
  end if;
end if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `addNewCoinPriceMatchBuy`(IN `P_price` DECIMAL(14,8), IN `Coin_ID` INT, IN `User_ID` INT, IN `low_price` DECIMAL(14,8), IN `Name_ID` INT)
    MODIFIES SQL DATA
begin
  IF not EXISTS (SELECT * FROM `CoinPriceMatch` WHERE `CoinID` = Coin_ID and `Price` = P_price and `UserID` = User_ID and `LowPrice` = low_price) THEN

 INSERT INTO `CoinPriceMatch`( `CoinID`, `Price`, `UserID`,`LowPrice`) VALUES (Coin_ID, P_price, User_ID, low_price);

  END IF;

    INSERT INTO `CoinPriceMatchRules`(`CoinPriceMatchID`,`CoinPriceMatchNameID`) VALUES ((select `ID` from `CoinPriceMatch` where `Price` = P_price and `UserID` = User_ID and `LowPrice` = low_price and `CoinID` = Coin_ID), Name_ID);


    if not exists (select `ID` from `CoinPriceMatch` where `Price` = P_price and `UserID` = User_ID and `LowPrice` - low_price and `CoinID` = Coin_ID) THEN

    INSERT INTO `CoinPriceMatchRules`( `CoinPriceMatchID`, `CoinPriceMatchNameID`) VALUES (BuyRule_ID,(select `ID` from `CoinPriceMatch` where `Price` = P_price and `UserID` = User_ID and `LowPrice` = low_price and `CoinID` = Coin_ID), Name_ID);
    end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `add24HourPricewithBackup`(IN `new_Price` DECIMAL(20,8), IN `Coin_ID` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN
Declare Hr_24Price, Live_Price DEC(20,8);

if (new_Price <> 0.0) THEN
	Update `CoinPctChange` SET `Live24HrChange` = new_Price where `CoinID` = Coin_ID;
ELSE
    Update `CoinPctChange` SET `Live24HrChange` = (SELECT `24HrPrice` FROM `CMCData` WHERE `CoinID` = Coin_ID) where `CoinID` = Coin_ID;
End if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `addToBuyBackMultiply`(IN `BuyBackID` INT)
    MODIFIES SQL DATA
Begin
Declare nAddNum DEC(20,14);
Declare nBuyBackPct DEC(20,14);
Declare nLivePrice DEC(20,14);
Declare nMultiply INT;

SELECT `BuyBackPct` into nBuyBackPct FROM `BuyBack` WHERE `ID` = BuyBackID;
Select `LiveCoinPrice` into nLivePrice from `CoinPrice` where `CoinID` = (Select `CoinID` from `Transaction` where `ID` = (SELECT `TransactionID` from `BuyBack` where `ID` = BuyBackID));
Set nAddNum = ABS(nBuyBackPct)/(0.35*(ABS(nBuyBackPct)/10));
SET nMultiply = Abs((nBuyBackPct /100)*nAddNum);

UPDATE `BuyBack` SET `BuyBackPct` = (`BuyBackPct` + (nMultiply)) WHERE `BuyBackPct` < -1.75 and `ID` = BuyBackID and nAddNum > 0 and  nMultiply > 0;

End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `add1HrPattern`(IN `New_Pattern` VARCHAR(15), IN `User_ID` INT, IN `Name_ID` INT)
    MODIFIES SQL DATA
begin
  IF not EXISTS (SELECT * FROM `Coin1HrPattern` WHERE `Pattern` = New_Pattern) THEN
	INSERT INTO `Coin1HrPattern`(`Pattern`) VALUES (New_Pattern);
  end if;
  IF not EXISTS (SELECT * FROM `Coin1HrPatternRules` WHERE `Coin1HrPatternID` = (SELECT `ID` FROM `Coin1HrPattern` WHERE `Pattern` = New_Pattern) and `UserID` = User_ID) THEN
	INSERT INTO `Coin1HrPatternRules`( `Coin1HrPatternID`, `UserID`, `Coin1HrPatternNameID`) VALUES ((SELECT `ID` FROM `Coin1HrPattern` WHERE `Pattern` = New_Pattern),User_ID, Name_ID);
  end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `cancelSpreadBetTrackingSell`(IN `Trans_ID` INT)
    MODIFIES SQL DATA
BEGIN
Declare Current_SBID INT;
Declare new_SBRuleID INT;
Declare new_SBTransID INT;

SELECT `SpreadBetTransactionID` into Current_SBID FROM `Transaction` WHERE `ID` = Trans_ID;

SELECT `SpreadBetRuleID` into new_SBRuleID FROM `UserConfig` WHERE `UserID` = (SELECT `UserID` from `Transaction` where `ID` = Trans_ID);

Select `ID` into new_SBTransID from `SpreadBetTransactions` where `SpreadBetRuleID` = new_SBRuleID;

UPDATE `Transaction` SET `SpreadBetTransactionID` = new_SBTransID, `SpreadBetRuleID` = new_SBRuleID where `SpreadBetTransactionID` = Current_SBID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `addPricePattern`(IN `Price_Pattern` VARCHAR(15), IN `User_ID` INT, IN `Name_ID` INT)
    MODIFIES SQL DATA
begin
  IF not EXISTS (SELECT * FROM `CoinPricePattern` WHERE `CoinPattern` = Price_Pattern) THEN
	INSERT INTO `CoinPricePattern`(`CoinPattern`) VALUES (Price_Pattern);
end if;
IF not EXISTS (SELECT * FROM `CoinPricePatternRules` WHERE `PatternID` = (SELECT `ID` FROM `CoinPricePattern` WHERE `CoinPattern` = Price_Pattern) and `UserID` = User_ID and `CoinPricePatternNameID` = Name_ID) THEN
	INSERT INTO `CoinPricePatternRules`( `PatternID`, `UserID`,`CoinPricePatternNameID`) VALUES ((SELECT `ID` FROM `CoinPricePattern` WHERE `CoinPattern` = Price_Pattern),User_ID, Name_ID);
end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `addWebSavings`(IN `User_ID` INT, IN `Total_USD` DECIMAL(20,8), IN `Live_Price` DECIMAL(20,8), IN `nProfit` DECIMAL(20,8))
    MODIFIES SQL DATA
begin

if EXISTS(SELECT `UserID` FROM `WebSavings` WHERE `UserID` = User_ID) THEN
	UPDATE `WebSavings` SET `TotalUSDT`=Total_USD, `LivePrice` = Live_Price, `Profit` = nProfit WHERE `UserID` = User_ID;
ELSE
	INSERT INTO `WebSavings`(`UserID`, `TotalUSDT`, `LivePrice`, `Profit`) VALUES (User_ID,Total_USD,Live_Price,nProfit);
END IF;
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `cancelTrackingSellUpdateSBTransID`(IN `Trans_ID` INT)
    MODIFIES SQL DATA
BEGIN

Declare SB_RuleID INT;
Declare SB_TransID_User INT;
Declare SB_TransID_table INT;

SELECT `SpreadBetRuleID` into SB_RuleID FROM `Transaction` WHERE `ID` = Trans_ID;
SELECT `ID` into SB_TransID_table FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = SB_RuleID;
SELECT `SpreadBetTransactionID` into SB_TransID_User FROM `Transaction` WHERE `ID` = Trans_ID;

if SB_TransID_User <> SB_TransID_table THEN
	UPDATE `Transaction` SET `SpreadBetTransactionID` = SB_TransID_table where  `SpreadBetTransactionID` = SB_TransID_User;
END IF;
UPDATE `TrackingSellCoins` SET `Status`= 'Closed' WHERE `TransactionID` = Trans_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `newCopyTransNewAmount`(IN `nQuantity` DECIMAL(20,14), IN `oQuantity` DECIMAL(20,14), IN `Trans_ID` INT, IN `nOrder_Number` VARCHAR(50))
    MODIFIES SQL DATA
BEGIN
Insert into `Transaction` (`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`OrderNo`,`BuyOrderCancelTime`,`SellOrderCancelTime`,`FixSellRule`,`BuyRule`,`SellRule`)
SELECT `Type`,`CoinID`,`UserID`,`CoinPrice`,nQuantity,`Status`,`OrderDate`,`CompletionDate`,nOrder_Number,`BuyOrderCancelTime`,`SellOrderCancelTime`,`FixSellRule`,`BuyRule`,`SellRule` FROM `Transaction` WHERE `ID` = Trans_ID;
Update `Transaction` set `Amount` = oQuantity WHERE `ID` = Trans_ID;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `newUpdatePctChange`(IN `Coin_ID` INT, IN `Hr1_Pct` DECIMAL(20,8), IN `Hr24_Pct` DECIMAL(20,8), IN `D7_Pct` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN
UPDATE `CoinPctChange` SET `Last7DChange` = `Live7DChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Last24HrChange` = `Live24HrChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Last1HrChange` = `Live1HrChange`  WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live7DChange` = D7_Pct WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live24HrChange` = Hr24_Pct WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live1HrChange` = Hr1_Pct WHERE `CoinID` = Coin_ID;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `setLowMarketMode`(IN `User_ID` INT, IN `nMins` INT, IN `nMode` INT)
    MODIFIES SQL DATA
BEGIN
Declare currentMode INT;
DECLARE usdtHolding DEC(20,14);
DECLARE btcHolding DEC(20,14);
DECLARE ethHolding DEC(20,14);
DECLARE usdtSaving DEC(20,14);
DECLARE btcSaving DEC(20,14);
DECLARE ethSaving DEC(20,14);
DECLARE finalUSDTAmount DEC(20,14);
DECLARE finalBTCAmount DEC(20,14);
DECLARE finalETHAmount DEC(20,14);

SELECT `LowMarketModeEnabled` into currentMode FROM `UserConfig` WHERE `UserID` = User_ID;
SELECT `Total` into btcHolding FROM `BittrexBalances` WHERE `UserID` = User_ID and `Symbol` = 'BTC';
SELECT `Total` into usdtHolding FROM `BittrexBalances` WHERE `UserID` = User_ID and `Symbol` = 'USDT';
SELECT `Total` into ethHolding FROM `BittrexBalances` WHERE `UserID` = User_ID and `Symbol` = 'ETH';
SELECT `SavingUSDT` into usdtSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;
SELECT `SavingBTC` into btcSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;
SELECT `SavingETH` into ethSaving FROM `UserCoinSavings` WHERE `UserID` = User_ID;

UPDATE `UserConfig` SET `LowMarketModeDate` = date_add(now(),INTERVAL nMins MINUTE), `LowMarketModeEnabled` = nMode
where `UserID` = User_ID;


if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 1) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'USDT',1);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 2) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'USDT',2);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 3) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'USDT',3);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 4) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'USDT',4);
end if;

if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 1) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'BTC',1);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 2) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'BTC',2);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 3) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'BTC',3);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 4) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'BTC',4);
end if;

if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 1) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'ETH',1);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 2) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'ETH',2);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 3) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'ETH',3);
end if;
if NOT EXISTS (SELECT `ID` FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 4) THEN
  INSERT INTO `UserCoinAllocationAmounts`( `UserID`, `BaseCurrency`, `CoinAllocationID`) VALUES (User_ID,'ETH',4);
end if;

if (currentMode = -1 AND nMode >= 1) THEN
  SET finalUSDTAmount = (usdtHolding - usdtSaving)/4;
  SET finalBTCAmount = (btcHolding - btcSaving)/4;
  SET finalETHAmount = (ethHolding - ethSaving)/4;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalUSDTAmount,`BaseCurrency`='USDT',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 1;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalUSDTAmount,`BaseCurrency`='USDT',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 2;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalUSDTAmount,`BaseCurrency`='USDT',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 3;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalUSDTAmount,`BaseCurrency`='USDT',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 4;

  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalBTCAmount,`BaseCurrency`='BTC',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 1;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalBTCAmount,`BaseCurrency`='BTC',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 2;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalBTCAmount,`BaseCurrency`='BTC',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 3;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalBTCAmount,`BaseCurrency`='BTC',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 4;

  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalETHAmount,`BaseCurrency`='ETH',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 1;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalETHAmount,`BaseCurrency`='ETH',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 2;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalETHAmount,`BaseCurrency`='ETH',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 3;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalETHAmount,`BaseCurrency`='ETH',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 4;

elseif (currentMode >= 1 AND nMode = -1) THEN
  SET finalUSDTAmount = 0;
  SET finalBTCAmount = 0;
  SET finalETHAmount = 0;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalUSDTAmount,`BaseCurrency`='USDT',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 1;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalUSDTAmount,`BaseCurrency`='USDT',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 2;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalUSDTAmount,`BaseCurrency`='USDT',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 3;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalUSDTAmount,`BaseCurrency`='USDT',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'USDT' and `CoinAllocationID` = 4;

  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalBTCAmount,`BaseCurrency`='BTC',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 1;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalBTCAmount,`BaseCurrency`='BTC',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 2;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalBTCAmount,`BaseCurrency`='BTC',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 3;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalBTCAmount,`BaseCurrency`='BTC',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'BTC' and `CoinAllocationID` = 4;

  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalETHAmount,`BaseCurrency`='ETH',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 1;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalETHAmount,`BaseCurrency`='ETH',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 2;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalETHAmount,`BaseCurrency`='ETH',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 3;
  UPDATE `UserCoinAllocationAmounts` SET `UserID`= User_ID,`Amount`=finalETHAmount,`BaseCurrency`='ETH',`CoinAllocationID`= 1 WHERE `UserID` = User_ID and `BaseCurrency` = 'ETH' and `CoinAllocationID` = 4;

end if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `subUSDTBal`(IN `n_Sym` VARCHAR(50), IN `n_usdtPurchase` DECIMAL(20,8), IN `User_ID` INT, IN `n_Price` DECIMAL(20,8))
    MODIFIES SQL DATA
begin

  IF EXISTS (SELECT `ID` FROM `BittrexBalances` WHERE `Symbol` = n_Sym and day(`Date`) = day(now()) and month(`Date`) = month(now()) and year(`Date`) = year(now()) ) THEN
   	UPDATE `BittrexBalances` SET `Total` = `Total` - n_usdtPurchase, `Price` = n_Price WHERE `UserID` = User_ID and `Symbol` = n_Sym and day(`Date`) = day(now()) and month(`Date`) = month(now()) and year(`Date`) = year(now()) ;
 ELSE
	INSERT INTO `BittrexBalances`(`Symbol`, `Total`, `Price`,`UserID`)
    select `Symbol`, `Total` - n_usdtPurchase, `Price`,`UserID` from `BittrexBalances` where `UserID` = User_ID and `Symbol` = n_Sym order by `Date` DESC limit 1 ;
  END IF;
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `deleteSecondaryRules`(IN `n_ID` INT)
    MODIFIES SQL DATA
BEGIN
DECLARE cmr_ID INT;
 if NOT EXISTS (SELECT * FROM `Transaction` WHERE `FixSellRule` = n_ID and `Status` in ('Open','Pending','Saving')) THEN
		DELETE FROM `SellRules` WHERE `ID` = n_ID;
    	SELECT `ID` INTO cmr_ID FROM `CoinModeRules` WHERE `SecondarySellRules` like concat('%',n_ID,'%');
    	UPDATE `CoinModeRules` SET `SecondarySellRules`= replace(`SecondarySellRules`, concat(n_ID,','), '') WHERE `ID` = (cmr_ID);
 end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateMarketAlertRuleID`()
    MODIFIES SQL DATA
Begin
DELETE FROM `MarketAlertsRule`;

INSERT INTO `MarketAlertsRule`( `Name`) VALUES ('MarketAlert');
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateOvernightBuyBackPct`(IN `BB_PCT` DECIMAL(20,14), IN `BB_ID` INT)
    MODIFIES SQL DATA
BEGIN

IF BB_PCT < -10.0 THEN
	Update `BuyBack` SET `BuyBackPct` = `BuyBackPct` + 10 Where `ID` = BB_ID;
END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateSpreadBetRuleID`()
    MODIFIES SQL DATA
Begin
DELETE FROM `SpreadBetAlertsRule`;
INSERT INTO `SpreadBetAlertsRule`(`Name`) VALUES ('SpreadBetRule');
end$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `newLogToSQL`(IN `User_ID` INT, IN `In_Sub` VARCHAR(100), IN `In_Comments` TEXT, IN `save_Total` INT, IN `Sub_Title` VARCHAR(50), IN `nRef` VARCHAR(50))
    MODIFIES SQL DATA
BEGIN
  DECLARE CountOfAction INTEGER;

  SELECT Count(`ID`) INTO CountOfAction
	FROM `ActionLog`
	WHERE `UserID` = User_ID and `Subject` = In_Sub ;

  IF (CountOfAction > save_Total) THEN
    DELETE FROM `ActionLog` WHERE `UserID` = User_ID and `Subject` = In_Sub Order by `ID` limit 1;

  END IF;
INSERT INTO `ActionLog`(`UserID`, `Subject`, `Comment`,`SubTitle`, `Reference`) VALUES (User_ID,In_Sub,In_Comments, Sub_Title, nRef);

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateSpreadProfit`(IN `Spread_Bet_RuleID` INT, IN `PctProfit` DECIMAL(20,8))
    MODIFIES SQL DATA
BEGIN
Declare Pct_Profit_Sell DEC(20,8);
Select `LowestPctProfit` into Pct_Profit_Sell from `SpreadBetSettings` where `SpreadBetRuleID` = Spread_Bet_RuleID ;

UPDATE `SpreadBetSettings` SET `LowestPctProfit` = PctProfit WHERE  PctProfit < Pct_Profit_Sell and `SpreadBetRuleID` = Spread_Bet_RuleID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `writeBouncePrice`(IN `Top_Price` DECIMAL(20,14), IN `Low_Price` DECIMAL(20,14), IN `nDiff` DECIMAL(20,14), IN `Coin_ID` INT)
    MODIFIES SQL DATA
BEGIN

if EXists (SELECT `ID` FROM `BounceIndex` WHERE `CoinID` = Coin_ID) THEN
	UPDATE `BounceIndex` SET `TopPrice`= Top_Price,`LowPrice`= Low_Price,`Difference`= nDiff WHERE `CoinID` = Coin_ID;
ELSE
	INSERT INTO `BounceIndex`(`CoinID`, `TopPrice`, `LowPrice`, `Difference`) VALUES (Coin_ID,Top_Price, Low_Price, nDiff);
End If;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateBuyBackKittyAmount`(IN `Base_Curr` VARCHAR(10), IN `kitty_Amount` DECIMAL(20,14), IN `User_ID` INT)
    MODIFIES SQL DATA
BEGIN

if Base_Curr = 'USDT' and kitty_Amount > 0 THEN
    UPDATE `BuyBackKitty` SET `USDTAmount` = `USDTAmount` - kitty_Amount, `BuyPortion` = `BuyPortion` - 1 where `UserID` = User_ID;
elseif Base_Curr = 'BTC' and kitty_Amount > 0 THEN
    UPDATE `BuyBackKitty` SET `BTCAmount` = `BTCAmount` - kitty_Amount, `BuyPortionBTC` = `BuyPortionBTC` - 1 where `UserID` = User_ID;
elseif Base_Curr = 'ETH' and kitty_Amount > 0 THEN
    UPDATE `BuyBackKitty` SET `ETHAmount` = `ETHAmount` - kitty_Amount, `BuyPortionETH` = `BuyPortionETH` - 1 where `UserID` = User_ID;
end if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateSpreadSell`(IN `Spread_Bet_RuleID` INT, IN `OrderDate` DATETIME)
    MODIFIES SQL DATA
Begin
Declare Total_mins INT;
Declare mins_From_Buy INT;

Select TIMESTAMPDIFF(MINUTE,OrderDate,NOW()) into mins_From_Buy;

SELECT `NoOfTransactions`*`AvgTimeToSell` into Total_mins FROM `SpreadBetSettings` WHERE `SpreadBetRuleID` = Spread_Bet_RuleID;

UPDATE `SpreadBetSettings` set `NoOfTransactions` = (`NoOfTransactions` + 1) WHERE `SpreadBetRuleID` = Spread_Bet_RuleID;

UPDATE `SpreadBetSettings` set `AvgTimeToSell`= ((Total_mins + mins_From_Buy)/ `NoOfTransactions`) WHERE `SpreadBetRuleID` = Spread_Bet_RuleID;

End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddDelayToBuyBack`(IN `Coin_ID` INT, IN `n_Mins` INT, IN `User_ID` INT)
    NO SQL
BEGIN

Create Temporary Table tmpIDS
SELECT `Bbv`.`TransactionID`
FROM `BuyBack` `Bbv`
join `Transaction` `Tr` on `Tr`.`ID` = `Bbv`.`TransactionID`
where `Tr`.`CoinID` = Coin_ID and `UserID` = User_ID;

UPDATE `BuyBack` SET `DelayTime`= Date_Add(now(), INTERVAL n_Mins MINUTE)
where `TransactionID` in (Select `TransactionID` From tmpIDS);

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddBittrexBuy`(IN `Coin_ID` INT, IN `User_ID` INT, IN `nType` VARCHAR(50), IN `Bittrex_Ref` VARCHAR(200), IN `nStatus` VARCHAR(10), IN `rule_ID` INT, IN `nCost` DECIMAL(20,14), IN `nAmount` DECIMAL(20,14), IN `Order_No` VARCHAR(150), IN `Cancel_Time` INT, IN `Sell_Rule_Fixed` INT)
    MODIFIES SQL DATA
BEGIN
DECLARE newDate Date;

SELECT DATE_ADD(now(),INTERVAL Cancel_Time MINUTE) into newDate;

INSERT INTO `Transaction`(`Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `OrderNo`, `BittrexRef`,  `BuyRule`, `ToMerge`, `NoOfPurchases`, `NoOfCoinSwapsThisWeek`, `NoOfCoinSwapPriceOverrides`, `SpreadBetTransactionID`, `SpreadBetRuleID`, `OverrideCoinAllocation`,`FixSellRule`) VALUES (nType,Coin_ID,User_ID,nCost,nAmount,'Pending', now(),Order_No,Bittrex_Ref, rule_ID,1,0,0,0,0,0,0,Sell_Rule_Fixed);
INSERT INTO `BittrexAction`(`CoinID`, `TransactionID`, `UserID`, `Type`, `BittrexRef`, `ActionDate`, `Status`, `RuleID`,`MinsToCancelAction`) VALUES (Coin_ID,(SELECT `ID` from `Transaction` Where `BittrexRef` = Bittrex_Ref),User_ID,nType,Bittrex_Ref,now(),nStatus,rule_ID,Cancel_Time);
update `Transaction` set `BittrexID` = (SELECT `ID` from `BittrexAction` where `BittrexRef` = Bittrex_Ref) where `BittrexRef` = Bittrex_Ref;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `PriceDipEnable`(IN `n_status` INT, IN `rule_ID` INT)
    MODIFIES SQL DATA
BEGIN
declare c_status INT;
Select  `PriceDipEnabled` into c_status from `PriceDipStatus` WHERE `BuyRuleID` = rule_ID;
	UPDATE `PriceDipStatus` SET `PriceDipEnabled`= n_status WHERE `BuyRuleID` = rule_ID;
 if (c_status = 1 AND n_Status = 0) Then
	UPDATE `PriceDipStatus` SET `DipStartTime` = DATE_ADD(now(), INTERVAL 1 YEAR) WHERE `BuyRuleID` = rule_ID;
 ELSEIF (c_status = 0 AND n_Status = 1) THEN
 	UPDATE `PriceDipStatus` SET `DipStartTime` = now()   WHERE `BuyRuleID` = rule_ID;
 end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CompleteBittrexBuy`(IN `Bittrex_Ref` VARCHAR(200), IN `Trans_ID` INT, IN `Final_Price` DECIMAL(20,14))
    MODIFIES SQL DATA
BEGIN
Update `Transaction` SET `Status` = 'Open', `CoinPrice` = Final_Price where `ID` = Trans_ID;
Update `BittrexAction` SET `Status` = 'Closed' where `TransactionID` = Trans_ID;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CancelBittrexBuy`(IN `Bittrex_Ref` VARCHAR(200), IN `Trans_ID` INT)
    MODIFIES SQL DATA
BEGIN
Update `Transaction` SET `Status` = 'Cancelled' where `ID` = Trans_ID;
Update `BittrexAction` SET `Status` = 'Cancelled' where `TransactionID` = Trans_ID;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updatePriceDipHours`(IN `rule_ID` INT, IN `hours_Flat` INT)
    MODIFIES SQL DATA
BEGIN

If NOT EXISTS(SELECT `BuyRuleID` FROM `PriceDipStatus` WHERE `BuyRuleID` = rule_ID) THEN
	INSERT INTO `PriceDipStatus`(`BuyRuleID`) VALUES (rule_ID);
END IF;

UPDATE `PriceDipStatus` SET `HoursFlat` = hours_Flat WHERE `BuyRuleID` = rule_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddPctPrices`(IN `Coin_ID` INT, IN `Price_1Hr` DECIMAL(20,14), IN `Price_24Hr` DECIMAL(20,14), IN `Price_7D` DECIMAL(20,14), IN `Price_15Min` DECIMAL(20,14), IN `Price_30Min` DECIMAL(20,14), IN `Price_45Min` DECIMAL(20,14), IN `Price_75Min` DECIMAL(20,14), IN `Price_48Hr` DECIMAL(20,14), IN `Price_72Hr` DECIMAL(20,14))
    MODIFIES SQL DATA
BEGIN

If not EXISTS (SELECT `CoinID` FROM `CoinPctChange` WHERE `CoinID` = Coin_ID) THEN
INSERT INTO `CoinPctChange`(`CoinID`) VALUES (Coin_ID);
ENDIF;

UPDATE `CoinPctChange` SET `1HrChange5`=`1HrChange4` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `1HrChange4`=`1HrChange3` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `1HrChange3`=`Last1HrChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Last1HrChange`=`Live1HrChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Last24HrChange`=`Live24HrChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Last7DChange`=`Live7DChange` WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live1HrChange`=Price_1Hr WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live24HrChange`=Price_24Hr WHERE `CoinID` = Coin_ID;
UPDATE `CoinPctChange` SET `Live7DChange`=Price_7D WHERE `CoinID` = Coin_ID;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `NewBuySellProfitSetup`(IN `Coin_ID` INT, IN `User_ID` INT, IN `Enable_Buy` INT, IN `Enable_Sell` INT, IN `nPct` DECIMAL(20,14), IN `nSellRule` INT)
    MODIFIES SQL DATA
Begin

Declare nBuyRule INT;

SELECT `ID` INTO nBuyRule FROM `BuyRules` WHERE `SellRuleFixed` = nSellRule;

Update `BuyRules` SET `SellRuleFixed` = nSellRule, `UserID` = User_ID,  `BuyPriceMinEnabled` = 1, `BuyPriceMin` = AvgMinPrice(Coin_ID,nPct) where `ID` = nBuyRule;
Update `SellRules` SET `ReEnableBuyRule` = nBuyRule, `LimitToBuyRule` = nBuyRule, `UserID` = User_ID, `SellPriceMinEnabled` = 1, `SellPriceMin` = AvgMaxPrice(Coin_ID,nPct) where `ID` = nSellRule;

if (Enable_Buy = 1) THEN
Update `BuyRules` SET  `BuyCoin` = 1 where `ID` = nBuyRule;
END IF;

if (Enable_Sell = 1) THEN
Update `SellRules` SET `SellCoin` = 1  where `ID` = nSellRule;
END IF;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CancelBuySellProfit`(IN `Rule_ID` INT, IN `User_ID` INT, IN `Coin_ID` INT)
    MODIFIES SQL DATA
BEGIN
Declare nSymbol VARCHAR(20);

Select `Symbol` INTO nSymbol FROM `Coins` where `ID` = Coin_ID;

UPDATE `BuyRules` SET  `BuyCoin` = 1 where `RuleName` = concat('BuySellProfit ', nSymbol) and `UserID` = User_ID and `ID` = Rule_ID;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updatePriceDipCoinHours`(IN `Coin_ID` INT, IN `nHours` INT, IN `nLowHours` INT, IN `nHighHours` INT)
    MODIFIES SQL DATA
BEGIN

If NOT Exists (SELECT `ID` FROM `PriceDipCoinStatus` WHERE `CoinID` = Coin_ID) THEN
	INSERT INTO `PriceDipCoinStatus`(`CoinID`) VALUES (Coin_ID);
End IF;

UPDATE `PriceDipCoinStatus` SET `HoursFlat`= nHours, `HoursFlatLow`= nLowHours,`HoursFlatHigh` = nHighHours WHERE `CoinID` = Coin_ID;

END$$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddAvgCoinPrice`(IN `Coin_ID` INT, IN `High_Low` VARCHAR(30))
    MODIFIES SQL DATA
BEGIN

Declare month3Avg DEC(20,14);
Declare month6Avg DEC(20,14);
Declare daysFromUpdate INT;

SELECT DATEDIFF(CURDATE(),`LastUpdated`) AS DateDiff into daysFromUpdate FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = High_Low;

If NOT EXISTS (SELECT `ID` FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = High_Low) THEN
INSERT INTO `AvgHighLow`( `CoinID`, `HighLow`) VALUES (Coin_ID,High_Low);
SET daysFromUpdate = 91;
END IF;

if (daysFromUpdate >= 90) THEN
if High_Low = 'High' THEN
SELECT MAX(`MaxPrice`) into month3Avg FROM `MonthlyMaxPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL 3 MONTH);

SELECT MAX(`MaxPrice`) into month6Avg FROM `MonthlyMaxPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL 6 MONTH);

else
SELECT MIN(`MinPrice`) INTO month3Avg FROM `MonthlyMinPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL 3 MONTH);

SELECT MIN(`MinPrice`) INTO month6Avg FROM `MonthlyMinPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL 6 MONTH);

END IF;
Update `AvgHighLow` SET `HighLow` = High_Low, `3MonthPrice` = month3Avg, `6MonthPrice` = month6Avg, `LastUpdated` = CURRENT_DATE() where `CoinID` = Coin_ID and `HighLow` = High_Low;
END IF;


END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `writeCoinBuyHistoryStats`(IN `Coin_ID` INT, IN `bit_price` DECIMAL(20,14), IN `base_curr` VARCHAR(20), IN `nDate` DATETIME)
    MODIFIES SQL DATA
BEGIN
DECLARE last_Live DEC(20,14);
DECLARE nSymbol VARCHAR(20);

SELECT `LiveCoinPrice` INTO last_Live FROM `CoinBuyHistory` WHERE `ID` = Coin_ID order by `ActionDate` Desc Limit 1;

if (last_Live is null) THEN
	set last_Live = bit_price;
END IF;

Select `Symbol` INTO nSymbol from `Coin` where `ID` = Coin_ID;

INSERT INTO `CoinBuyHistory`(`ID`, `LiveCoinPrice`,`Symbol`,`BaseCurrency`,`ActionDate`,`LastCoinPrice`) VALUES (Coin_ID,bit_price,nSymbol ,base_curr,nDate,last_Live);

DELETE FROM `CoinBuyHistory` WHERE `ActionDate` < now() - interval 8 DAY;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `savingToLivewithMerge`(IN `User_ID` INT, IN `Coin_ID` INT, IN `Trans_ID` INT)
    MODIFIES SQL DATA
BEGIN

Declare SpreadBet_RuleID INT;
Declare SpreadBet_TransID INT;
Declare nStatus VARCHAR(50);

Select `SpreadbetRuleID` Into SpreadBet_RuleID FROM `Transaction` where `ID` = Trans_ID;
Select `SpreadbetTransactionID` Into SpreadBet_TransID FROM `Transaction` where `ID` = Trans_ID;
Select `Status` Into nStatus FROM `Transaction` where `ID` = Trans_ID;

IF EXISTS (SELECT `ID` from `Transaction` Where `Status` = 'Saving' and `CoinID`  = Coin_ID and `UserID` =  User_ID) THEN

UPDATE `Transaction` SET `Status` = nStatus, `ToMerge` = 1, `SpreadBetRuleID` = SpreadBet_RuleID, `SpreadBetTransactionID` = SpreadBet_TransID WHERE `CoinID` = Coin_ID and `UserID` = User_ID and `Status` = 'Saving';

UPDATE `Transaction` SET `ToMerge` = 1 WHERE `ID` = Trans_ID;

END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `setDefaultBuyRule`(IN `User_ID` INT, IN `Rule_ID` INT)
    MODIFIES SQL DATA
BEGIN
	UPDATE `BuyRules` SET `DefaultRule` = 0 where `UserID` = User_ID;
	UPDATE `BuyRules` SET `DefaultRule` = 1 where `ID` = Rule_ID;
End$$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CompleteBittrexSell`(IN `Bittrex_Ref` VARCHAR(50), IN `Trans_ID` INT, IN `Final_Price` DECIMAL(20,14))
    MODIFIES SQL DATA
BEGIN

	UPDATE `BittrexAction` SET `SellPrice` = Final_Price, `CompletionDate` = now(), `Status` = 'Closed',`BittrexRef` = Bittrex_Ref, `Type` = 'Sell' where  `TransactionID` = Trans_ID and `Type` = 'Sell';
	UPDATE `Transaction` SET `Status` = 'Sold', `CompletionDate` = now(),`BittrexRef` = Bittrex_Ref where `ID` = Trans_ID;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `AddToUserCoinSavings`(IN `User_ID` INT, IN `nSaving` DECIMAL(20,14), IN `nBase` VARCHAR(50))
    MODIFIES SQL DATA
BEGIN

if NOT Exists (SELECT `UserID` FROM `UserCoinSavings` WHERE `UserID` = User_ID) THEN
INSERT INTO `UserCoinSavings`(`UserID`) VALUES (User_ID);
end if;

if  nBase = 'USDT' THEN
UPDATE `UserCoinSavings` SET `SavingUSDT`= `SavingUSDT` + nSaving WHERE `UserID` = User_ID;
ELSEIF nBase = 'BTC' THEN
UPDATE `UserCoinSavings` SET `SavingBTC`= `SavingBTC` + nSaving WHERE `UserID` = User_ID;
ELSE
 UPDATE `UserCoinSavings` SET `SavingETH` = `SavingETH` + nSaving WHERE `UserID` = User_ID;
end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CancelBittrexSell`(IN `Bittrex_Ref` VARCHAR(100), IN `Trans_ID` INT)
    MODIFIES SQL DATA
BEGIN
	UPDATE `Transaction` SET `Status` = 'Open', `SellOrderCancelTime` = now()  WHERE `ID` = Trans_ID;
	UPDATE `BittrexAction` SET `Status` = 'Cancelled' where `BittrexRef` = Bittrex_Ref and `Type` in ('Sell','SpreadSell');
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `addOldBuyBackTransID`(IN `Trans_ID` INT, IN `Bittrex_ID` INT)
    MODIFIES SQL DATA
BEGIN
Declare BB_Trans_ID Int;
Declare MultiSellRule_TemplateID Int;
Declare Old_BB_Trans_ID Int;

SELECT `OldBuyBackTransID` into Old_BB_Trans_ID from `BittrexAction` Where `ID` = Bittrex_ID;

SELECT `MultiSellRuleTemplateID` into MultiSellRule_TemplateID FROM `Transaction` WHERE `ID` = Old_BB_Trans_ID;

SELECT `BuyBackTransactionID` into BB_Trans_ID FROM `Transaction` WHERE `ID` = Old_BB_Trans_ID;

if Old_BB_Trans_ID <> 0 THEN
	UPDATE `Transaction` SET `BuyBackTransactionID` = BB_Trans_ID Where `ID` = Trans_ID;
End if;

if MultiSellRule_TemplateID <> 0 Then
	UPDATE `Transaction` SET  `MultiSellRuleTemplateID` = MultiSellRule_TemplateID,`MultiSellRuleEnabled` = 1 Where `ID` = Trans_ID;
end if;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateMultiSellRuleConfig`(IN `Sell_Rule` INT, IN `User_ID` INT, IN `Trans_ID` INT)
    MODIFIES SQL DATA
BEGIN

If NOT EXISTS (SELECT `ID` FROM `MultiSellRuleConfig` WHERE `SellRuleID` = Sell_Rule and `UserID` = User_ID and `TransactionID` = Trans_ID ) THEN
INSERT INTO `MultiSellRuleConfig`(`SellRuleID`, `UserID`, `TransactionID`) VALUES (Sell_Rule,User_ID,Trans_ID);
END If;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `CompleteBittrexBuyUpdateAmount`(IN `Trans_ID` INT, IN `nAmount` DECIMAL(20,14))
    MODIFIES SQL DATA
Begin

UPDATE `BittrexAction` SET `Status` = 'Closed' WHERE `TransactionID` = Trans_ID;
UPDATE `Transaction` SET `Amount` = nAmount, `Status` = 'Open',`Type` = 'Sell' where `ID` = Trans_ID;

END$$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateCoinAllocationAfterPurchase`(IN `User_ID` INT, IN `nMode` INT, IN `BaseCurr` VARCHAR(50), IN `nAmount` DECIMAL(20,14))
    MODIFIES SQL DATA
BEGIN
DECLARE mode1 DEC(20,14);
DECLARE mode2 DEC(20,14);
DECLARE mode3 DEC(20,14);
DECLARE mode4 DEC(20,14);

SELECT `Amount` into mode1 FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 1;

SELECT `Amount` into mode2 FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 2;

SELECT `Amount` into mode3 FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 3;

SELECT `Amount` into mode4 FROM `UserCoinAllocationAmounts` WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 4;

if (nAmount <= mode1) THEN
	UPDATE `UserCoinAllocationAmounts` SET `Amount` = mode1-nAmount WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 1 and `Amount` > 0;
elseif (nAmount <= (mode1 + mode2)) THEN
UPDATE `UserCoinAllocationAmounts` SET `Amount` = 0 WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 1;
UPDATE `UserCoinAllocationAmounts` SET `Amount` = (mode1+mode2)-nAmount WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 2 and `Amount` > 0;
elseif (nAmount <= (mode1 + mode2 + mode3)) THEN
UPDATE `UserCoinAllocationAmounts` SET `Amount` = 0 WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 1;
UPDATE `UserCoinAllocationAmounts` SET `Amount` = 0 WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 2;
UPDATE `UserCoinAllocationAmounts` SET `Amount` = (mode1+mode2+mode3)-nAmount WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 3 and `Amount` > 0;
elseif (nAmount <= (mode1 + mode2 + mode3 + mode4)) THEN
UPDATE `UserCoinAllocationAmounts` SET `Amount` = 0 WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 1;
UPDATE `UserCoinAllocationAmounts` SET `Amount` = 0 WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 2;
UPDATE `UserCoinAllocationAmounts` SET `Amount` = 0 WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 3;
UPDATE `UserCoinAllocationAmounts` SET `Amount` = (mode1+mode2+mode3+mode4)-nAmount WHERE `UserID` = User_ID and `BaseCurrency` = BaseCurr and `CoinAllocationID` = 4 and `Amount` > 0;
end if;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `updateHoldingAmount`(IN `User_ID` INT, IN `base_curr` VARCHAR(50), IN `nAmount` DECIMAL(20,14), IN `Trans_ID` INT)
    MODIFIES SQL DATA
Begin
If (base_curr = 'USDT')  THEN
	UPDATE `UserCoinSavings` SET `HoldingUSDT` = nAmount WHERE `UserID` = User_ID;
ELSEIf (base_curr = 'BTC')  THEN
	UPDATE `UserCoinSavings` SET `HoldingBTC` = nAmount WHERE `UserID` = User_ID;
ELSEIf (base_curr = 'ETH')  THEN
	UPDATE `UserCoinSavings` SET `HoldingETH` = nAmount WHERE `UserID` = User_ID;
End if;
UPDATE `Transaction` SET `holdingAmount` = nAmount WHERE `ID` = Trans_ID;
End$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `RemoveHoldingAmount`(IN `User_ID` INT, IN `base_curr` VARCHAR(50), IN `nAmount` DECIMAL(20,14), IN `Trans_ID` INT)
    MODIFIES SQL DATA
Begin
If (base_curr = 'USDT')  THEN
	UPDATE `UserCoinSavings` SET `HoldingUSDT` = `HoldingUSDT` - nAmount WHERE `UserID` = User_ID;
ELSEIf (base_curr = 'BTC')  THEN
	UPDATE `UserCoinSavings` SET `HoldingBTC` = `HoldingBTC` - nAmount WHERE `UserID` = User_ID;
ELSEIf (base_curr = 'ETH')  THEN
	UPDATE `UserCoinSavings` SET `HoldingETH` =  `HoldingETH` - nAmount WHERE `UserID` = User_ID;
End if;
UPDATE `Transaction` SET `holdingAmount` = `holdingAmount` - nAmount WHERE `ID` = Trans_ID;
End$$
DELIMITER ;
