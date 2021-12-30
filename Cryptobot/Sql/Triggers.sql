CREATE TRIGGER `Add_Coin_ID` AFTER INSERT ON `Coin`
 FOR EACH ROW BEGIN


INSERT INTO `CoinBuyOrders`(`CoinID`, `LiveBuyOrders`, `LastBuyOrders`) VALUES (NEW.ID, 0,0);
INSERT INTO `CoinMarketCap`(`CoinID`, `LiveMarketCap`, `LastMarketCap`) VALUES (NEW.ID, 0,0);

INSERT INTO `CoinPctChange`(`CoinID`, `Live1HrChange`, `Last1HrChange`, `Live24HrChange`, `Last24HrChange`, `Live7DChange`, `Last7DChange`) VALUES (NEW.ID,0,0,0,0,0,0);

INSERT INTO `CoinPrice`(`CoinID`, `LiveCoinPrice`, `LastCoinPrice`) VALUES (NEW.ID,0,0);

INSERT INTO `CoinSellOrders`(`CoinID`, `LiveSellOrders`, `LastSellOrders`) VALUES (NEW.ID,0,0);

INSERT INTO `CoinVolume`(`CoinID`, `LiveVolume`, `LastVolume`) VALUES (NEW.ID,0,0);

INSERT INTO `CoinStatsWeb`(`ID`, `Symbol`) VALUES (NEW.ID,NEW.Symbol);

INSERT INTO `AvgCoinPriceTableWeb`(`CoinID`,`Symbol`) VALUES (NEW.ID, NEW.Symbol);

INSERT INTO `AllCoinStatus`(`CoinID`) VALUES (NEW.ID);

INSERT INTO `CryptoAuto`(`CoinID`) VALUES (NEW.ID);
INSERT INTO `ProjectedPriceMax`(`CoinID`) VALUES (NEW.ID);

INSERT INTO `ProjectedPriceMin`(`CoinID`) VALUES (NEW.ID);
       END

CREATE TRIGGER `coins_after_delete` AFTER DELETE ON `Coin`
 FOR EACH ROW BEGIN
DELETE FROM `CoinBuyOrders` WHERE `CoinID` = OLD.`ID`;
DELETE FROM `CoinMarketCap` WHERE `CoinID` = OLD.`ID`;
DELETE FROM `CoinPctChange` WHERE `CoinID` = OLD.`ID`;
DELETE FROM `CoinPrice` WHERE `CoinID` = OLD.`ID`;
DELETE FROM `CoinSellOrders` WHERE `CoinID` = OLD.`ID`;
DELETE FROM `CoinVolume` WHERE `CoinID` = OLD.`ID`;
DELETE FROM `CoinStatsWeb` WHERE `ID` = OLD.`ID`;
DELETE FROM `AvgCoinPriceTableWeb` WHERE  `CoinID` = OLD.`ID`;
DELETE FROM `AllCoinStatus` WHERE  `CoinID` = OLD.`ID`;
DELETE FROM `CryptoAuto` WHERE  `CoinID` = OLD.`ID`;
END

CREATE TRIGGER `updateSellOrder` BEFORE INSERT ON `SellRules`
 FOR EACH ROW begin
    declare prev_num int;

    select Max(`CoinOrder`)
    into     prev_num
    from     `SellRules`;

    set NEW.CoinOrder=prev_num+10;
end

CREATE TRIGGER `dateadd` BEFORE INSERT ON `User`
 FOR EACH ROW BEGIN
	SET NEW.ExpiryDate =  DATE_ADD(CURRENT_TIMESTAMP(),INTERVAL 1 MONTH);
    INSERT INTO `BuyRules`(`UserID`) VALUES (NEW.`ID`);
    INSERT INTO `SellRules`(`UserID`) VALUES (NEW.`ID`);
END

CREATE TRIGGER `updateOrder` BEFORE INSERT ON `BuyRules`
 FOR EACH ROW begin
    declare prev_num INT;

    select Max(`CoinOrder`)
    into prev_num
    from `BuyRules`;

    set NEW.CoinOrder=prev_num+10;
end
