# Flags affectés par CMP

ZF = 1 si a == b    
ZF = 0 si a ≠ b    

SF = 1 si a < b (comparaison signée)    
SF = 0 si a >= b (comparaison signée)    

OF = 1 si un dépassement arithmétique se produit dans le cas des entiers signés.   
OF = 0 si aucun dépassement ne se produit.    

CF = 1 si a < b (comparaison non signée)    
CF = 0 si a >= b (comparaison non signée)    
 
PF = 1 si le nombre de bits à 1 dans le résultat est pair.    
PF = 0 si le nombre de bits à 1 dans le résultat est impair.    

AF = 1 si un emprunt se produit entre les bits 3 et 4 lors de la soustraction.    
AF = 0 si aucun emprunt ne se produit entre les bits 3 et 4.    

# Sauts

Tableau plus exhaustif : 
- https://www.felixcloutier.com/x86/jcc


JZ : saute si égalité (ZF = 1)    
JNZ : saute si inégalité (ZF = 0)    
JC : saute si Carry (CF = 1)    
JNC : saute si No Carry (CF = 0)    
JB : saute si Below (CF = 1)    
JBE : saute si Below ou Equal (CF = 1 ou ZF = 1)    
JA : saute si Above (CF = 0 et ZF = 0)    
JAE : saute si Above ou Equal (CF = 0)    
JL : saute si Less (SF ≠ OF)    
JGE : saute si Greater ou Equal (SF = OF)    
JLE : saute si Less ou Equal (ZF = 1 ou SF ≠ OF)    
JG : saute si Greater (ZF = 0 et SF = OF)    
