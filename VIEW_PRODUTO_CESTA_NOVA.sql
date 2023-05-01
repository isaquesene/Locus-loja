USE [locusqa]
GO

/****** Object:  View [dbo].[VIEW_PRODUTO_CESTA_NOVA]    Script Date: 27/04/2023 20:58:36 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE VIEW [dbo].[VIEW_PRODUTO_CESTA_NOVA]
AS
SELECT DISTINCT c1.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c1 ON a.agen_numero = c1.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c1.ces_pro1
UNION ALL
SELECT DISTINCT c2.ces_id AS ces_id, ces_id_agendamento AS ID_Agend, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c2 ON a.agen_numero = c2.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c2.ces_pro2
UNION ALL
SELECT DISTINCT c3.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c3 ON a.agen_numero = c3.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c3.ces_pro3
UNION ALL
SELECT DISTINCT c4.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c4 ON a.agen_numero = c4.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c4.ces_pro4
UNION ALL
SELECT DISTINCT c5.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c5 ON a.agen_numero = c5.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c5.ces_pro5
UNION ALL
SELECT DISTINCT c6.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c6 ON a.agen_numero = c6.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c6.ces_pro6
UNION ALL
SELECT DISTINCT c7.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c7 ON a.agen_numero = c7.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c7.ces_pro7
UNION ALL
SELECT DISTINCT c8.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c8 ON a.agen_numero = c8.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c8.ces_pro8
UNION ALL
SELECT DISTINCT c9.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c9 ON a.agen_numero = c9.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c9.ces_pro9
UNION ALL
SELECT DISTINCT c10.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c10 ON a.agen_numero = c10.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c10.ces_pro10
UNION ALL
SELECT DISTINCT c11.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c11 ON a.agen_numero = c11.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c11.ces_pro11
UNION ALL
SELECT DISTINCT c12.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c12 ON a.agen_numero = c12.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c12.ces_pro12
UNION ALL
SELECT DISTINCT c13.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c13 ON a.agen_numero = c13.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c13.ces_pro13
UNION ALL
SELECT DISTINCT c14.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c14 ON a.agen_numero = c14.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c14.ces_pro14
UNION ALL
SELECT DISTINCT c15.ces_id AS ces_id, ces_id_agendamento AS ces_id_agendamento, pro_cod_pro_cli AS pro_cod_pro_cli, pro_descricao AS pro_descricao
FROM     t_agendamentos a RIGHT JOIN
                  t_cestas c15 ON a.agen_numero = c15.ces_id_agendamento JOIN
                  t_produtos p ON p.pro_cod_pro_cli = c15.ces_pro15
UNION ALL
SELECT DISTINCT c16.ces_id AS ces_id,e.ent_id_agendamento AS ces_id_agendamento, e.ent_pro_id AS pro_cod_pro_cli, pp.pro_descricao AS pro_descricao
FROM     t_agendamentos a LEFT JOIN
                  t_entrada_manual e ON a.agen_numero = e.ent_id_agendamento LEFT JOIN
                  t_cestas c16 ON a.agen_numero = c16.ces_id_agendamento JOIN
                  t_produtos pp ON pp.pro_cod_pro_cli = e.ent_pro_id
WHERE  e.ent_id_agendamento IS NOT NULL
GO

EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[40] 4[20] 2[20] 3) )"
      End
      Begin PaneConfiguration = 1
         NumPanes = 3
         Configuration = "(H (1 [50] 4 [25] 3))"
      End
      Begin PaneConfiguration = 2
         NumPanes = 3
         Configuration = "(H (1 [50] 2 [25] 3))"
      End
      Begin PaneConfiguration = 3
         NumPanes = 3
         Configuration = "(H (4 [30] 2 [40] 3))"
      End
      Begin PaneConfiguration = 4
         NumPanes = 2
         Configuration = "(H (1 [56] 3))"
      End
      Begin PaneConfiguration = 5
         NumPanes = 2
         Configuration = "(H (2 [66] 3))"
      End
      Begin PaneConfiguration = 6
         NumPanes = 2
         Configuration = "(H (4 [50] 3))"
      End
      Begin PaneConfiguration = 7
         NumPanes = 1
         Configuration = "(V (3))"
      End
      Begin PaneConfiguration = 8
         NumPanes = 3
         Configuration = "(H (1[56] 4[18] 2) )"
      End
      Begin PaneConfiguration = 9
         NumPanes = 2
         Configuration = "(H (1 [75] 4))"
      End
      Begin PaneConfiguration = 10
         NumPanes = 2
         Configuration = "(H (1[66] 2) )"
      End
      Begin PaneConfiguration = 11
         NumPanes = 2
         Configuration = "(H (4 [60] 2))"
      End
      Begin PaneConfiguration = 12
         NumPanes = 1
         Configuration = "(H (1) )"
      End
      Begin PaneConfiguration = 13
         NumPanes = 1
         Configuration = "(V (4))"
      End
      Begin PaneConfiguration = 14
         NumPanes = 1
         Configuration = "(V (2))"
      End
      ActivePaneConfig = 0
   End
   Begin DiagramPane = 
      Begin Origin = 
         Top = 0
         Left = 0
      End
      Begin Tables = 
         Begin Table = "t_agendamentos"
            Begin Extent = 
               Top = 7
               Left = 48
               Bottom = 170
               Right = 277
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "t_cestas"
            Begin Extent = 
               Top = 7
               Left = 325
               Bottom = 170
               Right = 559
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "t_entrada_manual"
            Begin Extent = 
               Top = 7
               Left = 607
               Bottom = 148
               Right = 841
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "t_produtos"
            Begin Extent = 
               Top = 7
               Left = 889
               Bottom = 170
               Right = 1131
            End
            DisplayFlags = 280
            TopColumn = 0
         End
      End
   End
   Begin SQLPane = 
   End
   Begin DataPane = 
      Begin ParameterDefaults = ""
      End
   End
   Begin CriteriaPane = 
      Begin ColumnWidths = 11
         Column = 1440
         Alias = 900
         Table = 1170
         Output = 720
         Append = 1400
         NewValue = 1170
         SortType = 1350
         SortOrder = 1410
         GroupBy = 1350
         Filter = 1350
         Or = 1350
         Or = 1350
         Or = 1350
      End
   End
End
' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'VIEW',@level1name=N'VIEW_PRODUTO_CESTA_NOVA'
GO

EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPaneCount', @value=1 , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'VIEW',@level1name=N'VIEW_PRODUTO_CESTA_NOVA'
GO

