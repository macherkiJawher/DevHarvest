public class Main {
    public static void main(String[] args) {
        DepartementHashSet departementHashSet = new DepartementHashSet();

        Departement d1 = new Departement(1,50,"informatique");
        Departement d2 = new Departement(1,50,"informatique");
        Departement d3 = new Departement(3,10,"finance");
        Departement d4 = new Departement(6,20,"marketing");


        departementHashSet.ajouterDepartement(d1);
        departementHashSet.ajouterDepartement(d2);
        departementHashSet.ajouterDepartement(d3);
        departementHashSet.ajouterDepartement(d4);


        departementHashSet.displayDepartement();
        System.out.println(departementHashSet.rechercherDepartement(d1));
        System.out.println(departementHashSet.rechercherDepartement("finance"));

        System.out.println(departementHashSet.trierDepartementById());
        departementHashSet.supprimerDepartement(d3);
        departementHashSet.displayDepartement();
    }

}
