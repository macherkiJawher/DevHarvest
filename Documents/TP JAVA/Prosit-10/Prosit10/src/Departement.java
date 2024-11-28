import java.util.Objects;

public class Departement  implements Comparable<Departement>{
    int id , nbEmploye;
     String nomDept;

    public Departement() {
    }

    public Departement(int id, int nbEmploye, String nomDept) {
        this.id = id;
        this.nbEmploye = nbEmploye;
        this.nomDept = nomDept;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getNbEmploye() {
        return nbEmploye;
    }

    public void setNbEmploye(int nbEmploye) {
        this.nbEmploye = nbEmploye;
    }

    public String getNomDept() {
        return nomDept;
    }

    public void setNomDept(String nomDept) {
        this.nomDept = nomDept;
    }

    @Override
    public boolean equals(Object object) {
        if (this == object) return true;
        if (object instanceof Departement d) {
            return d.getNomDept().equals(nomDept) && this.id == d.getId() && d.getNbEmploye() == nbEmploye;
        }
        return  false;
    }

    @Override
    public int hashCode() {
        int result =17;
        result= 31*result+id;
        result= 31*result+nbEmploye;
        result= 31*result+nomDept.hashCode();
        return  result;
    }

    @Override
    public String toString() {
        return "Departement{" +
                "id=" + id +
                ", nbEmploye=" + nbEmploye +
                ", nomDept='" + nomDept + '\'' +
                '}';
    }

    @Override
    public int compareTo(Departement o) {
        return this.id-o.id;
    }
}
